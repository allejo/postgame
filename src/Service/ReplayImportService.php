<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use allejo\bzflag\networking\Packets\GamePacket;
use allejo\bzflag\networking\Packets\MsgAddPlayer;
use allejo\bzflag\networking\Packets\MsgAdminInfo;
use allejo\bzflag\networking\Packets\MsgCaptureFlag;
use allejo\bzflag\networking\Packets\MsgFlagDrop;
use allejo\bzflag\networking\Packets\MsgFlagGrab;
use allejo\bzflag\networking\Packets\MsgKilled;
use allejo\bzflag\networking\Packets\MsgMessage;
use allejo\bzflag\networking\Packets\MsgRemovePlayer;
use allejo\bzflag\networking\Packets\MsgTimeUpdate;
use allejo\bzflag\networking\Packets\PacketInvalidException;
use allejo\bzflag\networking\Replay as BZFlagReplay;
use App\Entity\CaptureEvent;
use App\Entity\ChatMessage;
use App\Entity\FlagUpdate;
use App\Entity\JoinEvent;
use App\Entity\KillEvent;
use App\Entity\PartEvent;
use App\Entity\PauseEvent;
use App\Entity\Player;
use App\Entity\Replay;
use App\Entity\ResumeEvent;
use App\Utility\BZChatTarget;
use App\Utility\BZTeamType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ReplayImportService
{
    /**
     * The maximum number of replays that should be imported in a single batch.
     * After this number, the entity manager should be cleared to avoid storing
     * too much in memory and triggering OOM errors.
     *
     * @var int
     */
    private const BATCH_SIZE = 10;

    /**
     * The internal count of how many replays have been imported in the current
     * batch. This number is reset to 0 each time the entity manager cache is
     * cleared.
     *
     * @var int
     */
    private static $BATCH_COUNT = 0;

    /** @var EntityManagerInterface */
    private $em;

    /** @var LoggerInterface */
    private $logger;

    /**
     * The current Replay object we're working with.
     *
     * @var Replay
     */
    private $currReplay;

    /**
     * An array of Player objects indexed by their callsign. For the duration of
     * a replay, the callsign will remain in this array as this is the only
     * "unique" value that will persist between rejoins.
     *
     * @var array<string, Player>
     */
    private $currPlayersByCallsign;

    /**
     * An array of Player objects indexed by their player ID.
     *
     * This array should have an accurate representation of player IDs as their
     * part and leave at any given time. This means that player IDs can be
     * recycled as players leaves and others join dictated by the replay packets.
     *
     * @var array<int, Player>
     */
    private $currPlayersByIndex;

    /**
     * An array storing a record of the team a player is currently a part of at
     * any given time throughout a session.
     *
     * This array is updated as players join and leave.
     *
     * @see BZTeamType For constants of team numerical values
     *
     * @var array<int, int>
     */
    private $currPlayersCurrentTeam;

    /**
     * An array storing the current JoinEvent.
     *
     * This array is updated as players join and leave. This record is stored to
     * have easy access when linking a PartEvent with the respective JoinEvent.
     *
     * @var array<int, JoinEvent>
     */
    private $currPlayersJoinRecord;

    /**
     * An array of packets intended for players who have not "joined" yet.
     *
     * Sometimes packets are sent out of order allowing for packets intended for
     * players to come before the `MsgAddPlayer` packet has come in and we've
     * recorded it. This is a queue for packets to handle once we have an actual
     * `MsgAddPlayer` packet come in.
     *
     * @var array<int, GamePacket>
     */
    private $currFuturePlayers;

    /**
     * An array of partial JoinEvent objects.
     *
     * For security purposes, BZFS transmits IPs in a separate packet intended
     * solely for game administrators meaning that JoinEvent objects cannot have
     * an IP address just with a `MsgAddPlayer` packet. Since a `MsgAddPlayer`
     * packet comes before a `MsgAdminInfo` packet, then we store the partial
     * JoinEvents in here and wait until we get a `MsgAdminInfo` for the
     * respective player ID so we can persist that to our EntityManager.
     *
     * @var JoinEvent[]
     */
    private $currPartialJoins;

    /**
     * The relative epoch timestamp of when this match has started. This epoch
     * timestamp will be updated each time a match pauses and resumes so we can
     * accurately get the amount of time left in a match by offsetting this
     * value by the amount of time we were paused.
     *
     * @var int
     */
    private $relativeStartTime;

    /**
     * A MsgTimeUpdate packet with a nagetive `timeLeft` value means the
     * countdown in a replay was paused. This variable will store the last time
     * the match was paused so we can offset the countdown time.
     *
     * This value should only have a value when a match has been "paused."
     *
     * @var MsgTimeUpdate|null
     */
    private $lastPausePacket;

    /** @var int The duration the current match is scheduled to be in seconds. */
    private $duration;

    /** @var array<int, string> A map of flag IDs to flag abbreviations */
    private $flagIDs;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Import a replay file into the database.
     *
     * @param string $filepath The filename or filepath to the replay to import
     * @param bool $dryRun Whether or not to actually write to the database
     * @param bool $doUpgrade Keep the replay ID but reimport all other information about the replay
     *
     * @throws \InvalidArgumentException when a non-existent file is given or a directory is given
     * @throws PacketInvalidException    when an invalid replay is given
     *
     * @return bool Returns true if the import was successful
     */
    public function importReplay(string $filepath, bool $dryRun, bool $doUpgrade): bool
    {
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException(sprintf('File not found: %s', $filepath));
        }

        if (!is_file($filepath)) {
            throw new \InvalidArgumentException(sprintf('A file must be given as a value for $filename'));
        }

        $this->initInstanceVariables();
        $replay = new BZFlagReplay($filepath);
        $filename = basename($filepath);
        $sha1 = sha1_file($filepath);

        $existing = $this->em->getRepository(Replay::class)->findOneBy([
            'fileHash' => $sha1,
        ]);

        // Don't import duplicate replays
        if (!empty($existing)) {
            if (!$doUpgrade) {
                return false;
            }

            $this->currReplay = $existing;
            $this->performUpgrade($dryRun);

            $this->logger->notice('Replay ID #{id} is being upgraded (hash: {hash})', [
                'id' => $this->currReplay->getId(),
                'hash' => substr($sha1, 0, 7),
            ]);
        } else {
            $this->currReplay = new Replay();

            // The hash should only ever be set on new imports and never changed after that
            $this->currReplay->setFileHash($sha1);

            // Only persist newly created replays
            $this->em->persist($this->currReplay);
        }

        $this->currReplay
            ->setFileName($filename)
            ->setStartTime($replay->getStartTime())
            ->setEndTime($replay->getEndTime())
        ;

        $this->relativeStartTime = $this->currReplay->getStartTime()->getTimestamp();

        foreach ($replay->getPacketsIterable() as $packet) {
            $this->handlePacket($packet);
        }

        if (!$dryRun) {
            ++self::$BATCH_COUNT;

            $this->em->flush();

            if (self::$BATCH_COUNT >= self::BATCH_SIZE) {
                self::$BATCH_COUNT = 0;

                $this->em->clear();
            }
        }

        return true;
    }

    private function initInstanceVariables(): void
    {
        $this->currPlayersByCallsign = [];
        $this->currPlayersByIndex = [
            253 => null,
        ];
        $this->currPlayersCurrentTeam = [
            253 => 'Observer',
        ];
        $this->currPlayersJoinRecord = [];
        $this->currFuturePlayers = [];
        $this->currPartialJoins = [];
        $this->lastPausePacket = null;
        $this->duration = null;
    }

    private function performUpgrade(bool $dryRun): void
    {
        $findQuery = [
            'replay' => $this->currReplay->getId(),
        ];

        $caps = $this->em->getRepository(CaptureEvent::class)->findBy($findQuery);
        $messages = $this->em->getRepository(ChatMessage::class)->findBy($findQuery);
        $flagUpdates = $this->em->getRepository(FlagUpdate::class)->findBy($findQuery);
        $joinEvents = $this->em->getRepository(JoinEvent::class)->findBy($findQuery);
        $killEvents = $this->em->getRepository(KillEvent::class)->findBy($findQuery);
        $partEvents = $this->em->getRepository(PartEvent::class)->findBy($findQuery);
        $pauseEvents = $this->em->getRepository(PauseEvent::class)->findBy($findQuery);
        $players = $this->em->getRepository(Player::class)->findBy($findQuery);
        $resumeEvents = $this->em->getRepository(ResumeEvent::class)->findBy($findQuery);

        $deletedEntities = array_merge(
            $caps,
            $messages,
            $flagUpdates,
            $joinEvents,
            $killEvents,
            $partEvents,
            $pauseEvents,
            $players,
            $resumeEvents
        );

        foreach ($deletedEntities as $entity) {
            $this->em->remove($entity);
            $this->em->detach($entity);
        }

        if (!$dryRun) {
            $this->em->flush();
        }
    }

    /**
     * Given any supported GamePacket, this method will forward on the request
     * to specialized method for that type of GamePacket.
     *
     * @param GamePacket $packet
     */
    private function handlePacket(GamePacket $packet): void
    {
        switch ($packet::PACKET_TYPE) {
            case MsgAddPlayer::PACKET_TYPE:
                $this->handleMsgAddPlayer($packet);
                break;

            case MsgAdminInfo::PACKET_TYPE:
                $this->handleMsgAdminInfo($packet);
                break;

            case MsgCaptureFlag::PACKET_TYPE:
                $this->handleMsgCaptureFlag($packet);
                break;

            case MsgFlagGrab::PACKET_TYPE:
                $this->handleMsgFlagUpdate($packet, true);
                break;

            case MsgFlagDrop::PACKET_TYPE:
                $this->handleMsgFlagUpdate($packet, false);
                break;

            case MsgKilled::PACKET_TYPE:
                $this->handleMsgKilled($packet);
                break;

            case MsgMessage::PACKET_TYPE:
                $this->handleMsgMessage($packet);
                break;

            case MsgRemovePlayer::PACKET_TYPE:
                $this->handleMsgRemovePlayer($packet);
                break;

            case MsgTimeUpdate::PACKET_TYPE:
                $this->handleMsgTimeUpdate($packet);
                break;

            default:
                break;
        }
    }

    private function handleMsgAddPlayer(MsgAddPlayer $packet): void
    {
        $player = null;
        $recordJoin = true;

        // This player has joined this replay at least once before, because we
        // have their callsign
        if (isset($this->currPlayersByCallsign[$packet->getCallsign()])) {
            $player = $this->currPlayersByCallsign[$packet->getCallsign()];
            $recordJoin = !isset($this->currPlayersByIndex[$packet->getPlayerIndex()]);
        }

        // We don't have a player cached meaning this is the first time the
        // player has joined, so let's try finding them in the database.
        if ($player === null) {
            $playerRepo = $this->em->getRepository(Player::class);
            $player = $playerRepo->findOneBy([
                'replay' => $this->currReplay,
                'callsign' => $packet->getCallsign(),
            ]);
        }

        // We don't have a record for this player for this replay, so let's
        // create a new instance.
        if ($player === null) {
            $player = new Player();
            $player
                ->setReplay($this->currReplay)
                ->setCallsign($packet->getCallsign())
            ;

            $this->em->persist($player);
        }

        $this->currPlayersByCallsign[$packet->getCallsign()] = $player;
        $this->currPlayersByIndex[$packet->getPlayerIndex()] = $player;
        $this->currPlayersCurrentTeam[$packet->getPlayerIndex()] = $packet->getTeamValue();

        // Only record the join in the database if it's an actual join
        // @todo To be honest, I can't remember how this boolean works
        if ($recordJoin) {
            $joinEvent = new JoinEvent();
            $joinEvent
                ->setReplay($this->currReplay)
                ->setTeam($packet->getTeamValue())
                ->setPlayer($player)
                ->setMotto($packet->getMotto())
                ->setTimestamp($packet->getTimestampAsDateTime())
                ->setMatchSeconds($this->calculateRealMatchTime($packet))
            ;

            $this->currPlayersJoinRecord[$packet->getPlayerIndex()] = $joinEvent;
            $this->currPartialJoins[$packet->getPlayerIndex()] = $joinEvent;
        }

        $this->dequeueFuturePlayer($packet->getPlayerIndex());
    }

    private function handleMsgAdminInfo(MsgAdminInfo $packet): void
    {
        foreach ($packet->getPlayers() as $player) {
            $playerIndex = $player->playerIndex;

            if (isset($this->currPartialJoins[$playerIndex])) {
                $join = $this->currPartialJoins[$playerIndex];
                $join
                    ->setIpAddress($player->ipAddress)
                ;

                $this->em->persist($join);

                unset($this->currPartialJoins[$playerIndex]);
            }
        }
    }

    private function handleMsgCaptureFlag(MsgCaptureFlag $packet): void
    {
        $captureEvent = new CaptureEvent();
        $captureEvent
            ->setReplay($this->currReplay)
            ->setCapper($this->currPlayersByIndex[$packet->getPlayerId()])
            ->setCapperTeam($this->currPlayersCurrentTeam[$packet->getPlayerId()])
            ->setCappedTeam($this->getTeamFromFlagId($packet->getFlagId()))
            ->setTimestamp($packet->getTimestampAsDateTime())
            ->setMatchSeconds($this->calculateRealMatchTime($packet))
        ;

        $this->em->persist($captureEvent);
    }

    /**
     * @param GamePacket|MsgFlagGrab|MsgFlagDrop $packet
     * @param bool                               $isGrab
     */
    private function handleMsgFlagUpdate(GamePacket $packet, bool $isGrab): void
    {
        // A flag grab event by definition will always happen before a flag
        // capture, so it's a cheating way of getting team values from flag IDs.
        //
        // Flag IDs don't change, so it's safe to always overwrite the values
        $this->flagIDs[$packet->getFlag()->index] = $packet->getFlag()->abbv;

        $flagEvent = new FlagUpdate();
        $flagEvent
            ->setReplay($this->currReplay)
            ->setPlayer($this->currPlayersByIndex[$packet->getPlayerId()])
            ->setIsGrab($isGrab)
            ->setFlagAbbv($packet->getFlag()->abbv)
            ->setPosX($packet->getFlag()->position[0])
            ->setPosY($packet->getFlag()->position[1])
            ->setPosZ($packet->getFlag()->position[2])
            ->setTimestamp($packet->getTimestampAsDateTime())
            ->setMatchSeconds($this->calculateRealMatchTime($packet))
        ;

        $this->em->persist($flagEvent);
    }

    private function handleMsgKilled(MsgKilled $packet): void
    {
        $killEvent = new KillEvent();
        $killEvent
            ->setReplay($this->currReplay)
            ->setVictim($this->currPlayersByIndex[$packet->getVictimId()])
            ->setVictimTeam($this->currPlayersCurrentTeam[$packet->getVictimId()])
            ->setKiller($this->currPlayersByIndex[$packet->getKillerId()])
            ->setKillerTeam($this->currPlayersCurrentTeam[$packet->getKillerId()])
            ->setTimestamp($packet->getTimestampAsDateTime())
            ->setMatchSeconds($this->calculateRealMatchTime($packet))
        ;

        $this->em->persist($killEvent);
    }

    private function handleMsgMessage(MsgMessage $packet): void
    {
        $pFrom = $packet->getPlayerFromId();
        $pTo = $packet->getPlayerToId();

        $message = new ChatMessage();
        $message
            ->setReplay($this->currReplay)
            ->setMessage($packet->getMessage())
            ->setTimestamp($packet->getTimestampAsDateTime())
            ->setMatchSeconds($this->calculateRealMatchTime($packet))
        ;

        if ($pFrom <= BZChatTarget::LAST_PLAYER) {
            $message
                ->setSender($this->currPlayersByIndex[$pFrom])
                ->setTeamFrom($this->currPlayersCurrentTeam[$pFrom])
            ;
        } else {
            $message->setTeamFrom($pFrom);
        }

        if ($pTo <= BZChatTarget::LAST_PLAYER) {
            // Somehow packets can be out of order and a server sent it before
            // the player joined
            if (!isset($this->currPlayersByIndex[$pTo])) {
                $this->queuePacket($pTo, $packet);

                return;
            }

            $message
                ->setRecipient($this->currPlayersByIndex[$pTo])
                ->setTeamTo($this->currPlayersCurrentTeam[$pTo])
            ;
        } else {
            $message->setTeamTo($pTo);
        }

        $this->em->persist($message);
    }

    private function handleMsgRemovePlayer(MsgRemovePlayer $packet): void
    {
        $playerId = $packet->getPlayerId();
        $player = $this->currPlayersByIndex[$playerId];

        $partEvent = new PartEvent();
        $partEvent
            ->setReplay($this->currReplay)
            ->setPlayer($player)
            ->setTimestamp($packet->getTimestampAsDateTime())
            ->setJoinEvent($this->currPlayersJoinRecord[$playerId])
            ->setMatchSeconds($this->calculateRealMatchTime($packet))
        ;

        $this->em->persist($partEvent);

        unset($this->currPlayersByIndex[$playerId]);
        unset($this->currPlayersCurrentTeam[$playerId]);
        unset($this->currPlayersJoinRecord[$playerId]);

        if (in_array($playerId, $this->currFuturePlayers)) {
            unset($this->currFuturePlayers[$playerId]);
        }
    }

    private function handleMsgTimeUpdate(MsgTimeUpdate $packet): void
    {
        // We have not been able to determine the duration of the match yet. This
        // can occur for a number of reasons:
        //
        // 1. This is the first MsgTimeUpdate packet we've received
        // 2. The first MsgTimeUpdate packet we've received had a remaining time
        //    of -1, which means that the match was paused before the first real
        //    MsgTimeUpdate could be sent.
        //
        // Keeping these facts in mind, let's do our best to grab the first
        // MsgTimeUpdate packet that looks to have a valid value.
        if ($this->duration === null && $packet->getTimeLeft() >= 0) {
            // BZFS sends a MsgTimeUpdate packet when a timed match starts
            // (https://git.io/fjRrK), which contains the expected duration of
            // the timed match. However, replays do not see this initial packet
            // because the bz_GameStartEndEventData_V2 is fired after
            // (https://git.io/fjRr6) the MsgTimeUpdate packet has been sent.
            //
            // The recording will only have the *second* MsgTimeUpdate packet
            // sent in an actual timed match. For this reason, we'll use this
            // hack to calculate the duration of a match in minutes and round up
            // to the nearest minute to get the value the first MsgTimeUpdate
            // packet would have had.
            //
            // e.g. 1169 / 60 = ciel(19.48) = 20 minutes

            $this->duration = (int)(ceil($packet->getTimeLeft() / 60) * 60);

            $this->currReplay->setDuration($this->duration);

            return;
        }

        // The countdown of a match was paused
        if ($packet->getTimeLeft() < 0) {
            $this->lastPausePacket = $packet;

            $event = new PauseEvent();
            $event
                ->setReplay($this->currReplay)
                ->setTimestamp($packet->getTimestampAsDateTime())
                ->setMatchSeconds($this->calculateRealMatchTime($packet))
            ;

            $this->em->persist($event);

            return;
        }

        // The match was paused and now we're continuing the countdown
        if ($this->lastPausePacket !== null) {
            $packetTime = $packet->getTimestampAsDateTime()->getTimestamp();
            $pausedTime = $this->lastPausePacket->getTimestampAsDateTime()->getTimestamp();

            // See how long we've been paused for so we can offset our "start time"
            $offset = $packetTime - $pausedTime;
            $this->relativeStartTime += $offset;

            $this->lastPausePacket = null;

            $event = new ResumeEvent();
            $event
                ->setReplay($this->currReplay)
                ->setTimestamp($packet->getTimestampAsDateTime())
                ->setMatchSeconds($this->calculateRealMatchTime($packet))
            ;

            $this->em->persist($event);
        }
    }

    /**
     * Get the number of seconds *into* a match we're currently in.
     *
     * @param GamePacket $packet
     *
     * @return int
     */
    private function calculateRealMatchTime(GamePacket $packet): int
    {
        return $packet->getTimestampAsDateTime()->getTimestamp() - $this->relativeStartTime;
    }

    /**
     * Queue a packet for future processing because the specified player ID does
     * not yet exist.
     *
     * @param int        $playerId
     * @param GamePacket $packet
     */
    private function queuePacket(int $playerId, GamePacket $packet): void
    {
        if (!isset($this->currFuturePlayers[$playerId])) {
            $this->currFuturePlayers[$playerId] = [];
        }

        $this->currFuturePlayers[$playerId][] = $packet;
    }

    /**
     * Loop through queued packets (if any) for a specified player ID and process
     * them now that the player ID exists.
     *
     * This method will safely requeue packets if the player ID still does not
     * exist.
     *
     * @param int $playerId
     */
    private function dequeueFuturePlayer(int $playerId): void
    {
        if (!isset($this->currFuturePlayers[$playerId])) {
            return;
        }

        $packets = $this->currFuturePlayers[$playerId];
        unset($this->currFuturePlayers[$playerId]);

        foreach ($packets as $packet) {
            $this->handlePacket($packet);
        }
    }

    /**
     * Get the team from a flag abbreviation.
     *
     * **Warning:** This is not safe for flag IDs that are not tied to team flags.
     *
     * @see BZTeamType
     *
     * @param int $flagId
     *
     * @return int the numerical representation of a team color
     */
    private function getTeamFromFlagId(int $flagId): int
    {
        $flagAbbv = $this->flagIDs[$flagId];
        $teams = [
            'R*' => BZTeamType::RED,
            'G*' => BZTeamType::GREEN,
            'B*' => BZTeamType::BLUE,
            'P*' => BZTeamType::PURPLE,
        ];

        return $teams[$flagAbbv];
    }
}
