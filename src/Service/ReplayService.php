<?php declare(strict_types=1);

namespace App\Service;

use allejo\bzflag\networking\Replay as BZFlagReplay;
use allejo\bzflag\networking\Packets\GamePacket;
use allejo\bzflag\networking\Packets\MsgAddPlayer;
use allejo\bzflag\networking\Packets\MsgAdminInfo;
use allejo\bzflag\networking\Packets\MsgCaptureFlag;
use allejo\bzflag\networking\Packets\MsgFlagDrop;
use allejo\bzflag\networking\Packets\MsgFlagGrab;
use allejo\bzflag\networking\Packets\MsgKilled;
use allejo\bzflag\networking\Packets\MsgMessage;
use allejo\bzflag\networking\Packets\MsgRemovePlayer;
use allejo\bzflag\networking\Packets\PacketInvalidException;
use App\Entity\CaptureEvent;
use App\Entity\ChatMessage;
use App\Entity\FlagUpdate;
use App\Entity\JoinEvent;
use App\Entity\KillEvent;
use App\Entity\PartEvent;
use App\Entity\Player;
use App\Entity\Replay;
use App\Utility\BZChatTarget;
use Doctrine\ORM\EntityManagerInterface;

class ReplayService
{
    /** @var EntityManagerInterface */
    private $em;

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

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Import a replay file into the database.
     *
     * @param string $filename The filename or filepath to the replay to import
     * @param bool   $dryRun   Whether or not to actually write to the database
     *
     * @throws \InvalidArgumentException when a non-existent file is given or a directory is given
     * @throws PacketInvalidException when an invalid replay is given
     */
    public function importReplay(string $filename, bool $dryRun): void
    {
        if (!file_exists($filename))
        {
            throw new \InvalidArgumentException(sprintf('File not found: %s', $filename));
        }

        if (!is_file($filename))
        {
            throw new \InvalidArgumentException(sprintf('A file must be given as a value for $filename'));
        }

        $this->initInstanceVariables();
        $replay = new BZFlagReplay($filename);

        $this->currReplay = new Replay();
        $this->currReplay
            ->setDuration($replay->getHeader()->getFileTimeAsSeconds())
            ->setFileName(basename($filename))
            ->setStartTime($replay->getStartTime())
            ->setEndTime($replay->getEndTime())
        ;

        $this->em->persist($this->currReplay);

        $packets = $replay->getPackets();
        foreach ($packets as $packet)
        {
            $this->handlePacket($packet);
        }

        if (!$dryRun)
        {
            $this->em->flush();
        }
    }

    private function initInstanceVariables(): void
    {
        $this->currPlayersByCallsign = [];
        $this->currPlayersByIndex = [
            253 => null,
        ];
        $this->currPlayersCurrentTeam = [
            253 => 'Observer'
        ];
        $this->currPlayersJoinRecord = [];
        $this->currFuturePlayers = [];
        $this->currPartialJoins = [];
    }

    /**
     * Given any supported GamePacket, this method will forward on the request
     * to specialized method for that type of GamePacket.
     *
     * @param GamePacket $packet
     */
    private function handlePacket(GamePacket $packet): void
    {
        switch ($packet::PACKET_TYPE)
        {
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
        if (isset($this->currPlayersByCallsign[$packet->getCallsign()]))
        {
            $player = $this->currPlayersByCallsign[$packet->getCallsign()];
            $recordJoin = !isset($this->currPlayersByIndex[$packet->getPlayerIndex()]);
        }

        // We don't have a player cached meaning this is the first time the
        // player has joined, so let's try finding them in the database.
        if ($player === null)
        {
            $playerRepo = $this->em->getRepository(Player::class);
            $player = $playerRepo->findOneBy([
                'replay' => $this->currReplay,
                'callsign' => $packet->getCallsign(),
            ]);
        }

        // We don't have a record for this player for this replay, so let's
        // create a new instance.
        if ($player === null)
        {
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
        if ($recordJoin)
        {
            $joinEvent = new JoinEvent();
            $joinEvent
                ->setReplay($this->currReplay)
                ->setTeam($packet->getTeamValue())
                ->setPlayer($player)
                ->setMotto($packet->getMotto())
                ->setTimestamp($packet->getTimestampAsDateTime())
            ;

            $this->currPlayersJoinRecord[$packet->getPlayerIndex()] = $joinEvent;
            $this->currPartialJoins[$packet->getPlayerIndex()] = $joinEvent;
        }

        $this->dequeueFuturePlayer($packet->getPlayerIndex());
    }

    private function handleMsgAdminInfo(MsgAdminInfo $packet): void
    {
        foreach ($packet->getPlayers() as $player)
        {
            $playerIndex = $player->playerIndex;

            if (isset($this->currPartialJoins[$playerIndex]))
            {
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
            ->setCappedTeam($packet->getTeam())
            ->setTimestamp($packet->getTimestampAsDateTime())
        ;

        $this->em->persist($captureEvent);
    }

    /**
     * @param GamePacket|MsgFlagGrab|MsgFlagDrop $packet
     * @param bool $isGrab
     */
    private function handleMsgFlagUpdate(GamePacket $packet, bool $isGrab): void
    {
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
        ;

        if ($pFrom <= BZChatTarget::LAST_PLAYER)
        {
            $message
                ->setPlayer($this->currPlayersByIndex[$pFrom])
                ->setTeamFrom($this->currPlayersCurrentTeam[$pFrom])
            ;
        }
        else
        {
            $message->setTeamFrom($pFrom);
        }

        if ($pTo <= BZChatTarget::LAST_PLAYER)
        {
            // Somehow packets can be out of order and a server sent it before
            // the player joined
            if (!isset($this->currPlayersByIndex[$pTo]))
            {
                $this->queuePacket($pTo, $packet);
                return;
            }

            $message
                ->setTarget($this->currPlayersByIndex[$pTo])
                ->setTeamTo($this->currPlayersCurrentTeam[$pTo])
            ;
        }
        else
        {
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
        ;

        $this->em->persist($partEvent);

        unset($this->currPlayersByIndex[$playerId]);
        unset($this->currPlayersCurrentTeam[$playerId]);
        unset($this->currPlayersJoinRecord[$playerId]);

        if (in_array($playerId, $this->currFuturePlayers))
        {
            unset($this->currFuturePlayers[$playerId]);
        }
    }

    /**
     * Queue a packet for future processing because the specified player ID does
     * not yet exist.
     *
     * @param int $playerId
     * @param GamePacket $packet
     */
    private function queuePacket(int $playerId, GamePacket $packet): void
    {
        if (!isset($this->currFuturePlayers[$playerId]))
        {
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
        if (!isset($this->currFuturePlayers[$playerId]))
        {
            return;
        }

        $packets = $this->currFuturePlayers[$playerId];
        unset($this->currFuturePlayers[$playerId]);

        foreach ($packets as $packet)
        {
            $this->handlePacket($packet);
        }
    }
}
