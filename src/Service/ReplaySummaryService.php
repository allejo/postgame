<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\CaptureEvent;
use App\Entity\ChatMessage;
use App\Entity\JoinEvent;
use App\Entity\KillEvent;
use App\Entity\PartEvent;
use App\Entity\Player;
use App\Entity\Replay;
use App\Utility\BZTeamType;
use App\Utility\DefaultArray;
use App\Utility\IMatchTimeEvent;
use App\Utility\MatchTime;
use App\Utility\SummaryCaptureRecord;
use App\Utility\SummaryChatMessage;
use App\Utility\SummaryDeathRecord;
use App\Utility\SummaryKillRecord;
use App\Utility\SummaryPlayerRecord;
use App\Utility\SummarySession;
use App\Utility\UnsummarizedException;
use App\Utility\WrongSummarizationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for creating summaries of Replays.
 *
 * This service supports two methods of summarizing: quick and full.
 *
 * **Quick Summary Contents**
 *
 * - Match duration total
 * - Team colors
 * - Final team score
 * - Capture records
 *
 * **Full Summary Contents**
 *
 * - Everything from a quick summary
 * - Player kill, death, team kill records
 * - Player versus records
 * - Player sessions + loyalty
 * - Player IP addresses
 */
class ReplaySummaryService
{
    /** @var int Default state for this service, where nothing has been summarized. */
    const UNSUMMARIZED = 0;

    /** @var int This service has summarized a replay in a quick manner. */
    const SUMMARIZED_QUICK = 10;

    /** @var int This service has summarized a replay completely. */
    const SUMMARIZED_FULL = 20;

    /** @var EntityManagerInterface */
    private $em;

    /** @var SummaryPlayerRecord[] */
    private $playerRecords;

    /** @var SummaryCaptureRecord[] */
    private $flagCaptures;

    /** @var SummaryChatMessage[] */
    private $chatMessages;

    /** @var DefaultArray */
    private $teamScores;

    /** @var int */
    private $summarized;

    /** @var int */
    private $duration;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->resetService();
    }

    /**
     * Get the type of summary this service has built.
     */
    public function getSummaryType(): int
    {
        return $this->summarized;
    }

    /**
     * Get all of the public chat messages sent in a replay.
     *
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     *
     * @return SummaryChatMessage[]
     */
    public function getChatMessages(): array
    {
        $this->requiresFullSummary();

        return $this->chatMessages;
    }

    /**
     * Get the duration of the replay in minutes.
     *
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    public function getDuration(): int
    {
        $this->requiresQuickSummary();

        return $this->duration;
    }

    /**
     * Get a list of all the flag captures that happened in this match.
     *
     * @throws UnsummarizedException
     * @throws WrongSummarizationException
     *
     * @return SummaryCaptureRecord[]
     */
    public function getFlagCaps(): array
    {
        $this->requiresQuickSummary();

        return $this->flagCaptures;
    }

    /**
     * @see BZTeamType
     *
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    public function getWinner(): int
    {
        $this->requiresQuickSummary();

        $winner = array_keys($this->teamScores->getAsArray(), max($this->teamScores->getAsArray()));

        return $winner[0] ?? -1;
    }

    /**
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    public function getWinnerScore(): int
    {
        $this->requiresQuickSummary();

        return $this->teamScores[$this->getWinner()];
    }

    /**
     * @see BZTeamType
     *
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    public function getLoser(): int
    {
        $this->requiresQuickSummary();

        $winner = $this->getWinner();
        $teams = $this->teamScores->getAsArray();
        unset($teams[$winner]);

        return array_keys($teams)[0] ?? -1;
    }

    /**
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    public function getLoserScore(): int
    {
        $this->requiresQuickSummary();

        return $this->teamScores[$this->getLoser()];
    }

    /**
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     *
     * @return SummaryPlayerRecord[]
     */
    public function getPlayerRecords(): array
    {
        $this->requiresFullSummary();

        return $this->playerRecords;
    }

    /**
     * Get a quick summary for a replay.
     */
    public function summarizeQuick(Replay $replay): void
    {
        $this->resetService();

        $findByFilter = [
            'replay' => $replay,
        ];

        $this->handleDuration($replay);
        $this->handleCaps($findByFilter);

        $this->summarized = self::SUMMARIZED_QUICK;
    }

    /**
     * Get a full summary for a replay.
     */
    public function summarizeFull(Replay $replay): void
    {
        $this->resetService();

        $findByFilter = [
            'replay' => $replay,
        ];

        $this->handleDuration($replay);
        $this->handlePlayers($findByFilter);
        $this->handleKillRecords($findByFilter);
        $this->handleJoins($findByFilter);
        $this->handleParts($findByFilter);
        $this->handleCaps($findByFilter);
        $this->handleTeamLoyalty($replay);
        $this->handleMessages($replay);

        $this->summarized = self::SUMMARIZED_FULL;
    }

    private function handlePlayers(array $findByFilter): void
    {
        $players = $this->em->getRepository(Player::class)->findBy($findByFilter);

        foreach ($players as $player) {
            $record = new SummaryPlayerRecord();
            $record->callsign = $player->getCallsign();

            $this->playerRecords[$player->getId()] = $record;
        }
    }

    private function handleKillRecords(array $findByFilter): void
    {
        $kills = $this->em->getRepository(KillEvent::class)->findBy($findByFilter);

        foreach ($kills as $kill) {
            $victim = $kill->getVictim();
            $killer = $kill->getKiller();

            // Somehow, we have a null victim? This is technically possible but
            // it should never happen.
            if (!$victim) {
                continue;
            }

            $victimId = $victim->getId();
            $killerId = $killer ? $killer->getId() : -1;

            ++$this->playerRecords[$victimId]->score->deaths;
            ++$this->playerRecords[$killerId]->score->kills;

            // This was a team kill
            if ($kill->getVictimTeam() === $kill->getKillerTeam()) {
                ++$this->playerRecords[$killerId]->score->teamKills;
            }

            $deathRecord = new SummaryDeathRecord();
            $deathRecord->killedBy = $killerId;
            $deathRecord->matchTime = $this->calculateMatchTime($kill);
            $deathRecord->timestamp = $kill->getTimestamp();

            $this->playerRecords[$victimId]->deaths[] = $deathRecord;
            ++$this->playerRecords[$victimId]->against[$killerId]->deaths;

            $killRecord = new SummaryKillRecord();
            $killRecord->victim = $victimId;
            $killRecord->matchTime = $this->calculateMatchTime($kill);
            $killRecord->timestamp = $kill->getTimestamp();

            $this->playerRecords[$killerId]->kills[] = $killRecord;
            ++$this->playerRecords[$killerId]->against[$victimId]->kills;
        }
    }

    private function handleJoins(array $findByFilter): void
    {
        $joins = $this->em->getRepository(JoinEvent::class)->findBy($findByFilter);

        foreach ($joins as $join) {
            $session = new SummarySession();
            $session->team = $join->getTeam();
            $session->ipAddress = $join->getIpAddress();
            $session->joinTime = $join->getTimestamp();

            $this->playerRecords[$join->getPlayer()->getId()]->sessions[] = $session;
        }
    }

    private function handleParts(array $findByFilter): void
    {
        $parts = $this->em->getRepository(PartEvent::class)->findBy($findByFilter);

        foreach ($parts as $part) {
            $playerId = $part->getPlayer()->getId();

            foreach ($this->playerRecords[$playerId]->sessions as &$session) {
                if (
                    $session->partTime === null &&
                    $part->getJoinEvent()->getTimestamp()->getTimestamp() === $session->joinTime->getTimestamp()
                ) {
                    $session->partTime = $part->getTimestamp();
                    $session->totalTime = $session->partTime->getTimestamp() - $session->joinTime->getTimestamp();

                    break;
                }
            }
        }
    }

    private function handleCaps(array $findByFilter): void
    {
        $caps = $this->em->getRepository(CaptureEvent::class)->findBy($findByFilter);

        foreach ($caps as $cap) {
            $capperId = $cap->getCapper()->getId();

            $record = new SummaryCaptureRecord();
            $record->playerId = $capperId;
            $record->cappingTeam = $cap->getCapperTeam();
            $record->cappingTeamScore = ++$this->teamScores[$cap->getCapperTeam()];
            $record->cappedTeam = $cap->getCappedTeam();
            $record->cappedTeamScore = $this->teamScores[$record->cappedTeam];
            $record->matchTime = $this->calculateMatchTime($cap);
            $record->timestamp = $cap->getTimestamp();

            $this->flagCaptures[] = $record;
            $this->playerRecords[$capperId]->flagCaptures[] = $record;
        }

        // If there were no flag captures this match, that means we need to get
        // the teams from player joins instead.
        if (empty($this->flagCaptures)) {
            if (count($this->playerRecords) === 1) {
                $this->handlePlayers($findByFilter);
                $this->handleJoins($findByFilter);
            }

            foreach ($this->playerRecords as $id => $pr) {
                // Skip the special SERVER player with id of -1 and players with
                // no sessions
                if ($id === -1 || count($pr->sessions) === 0) {
                    continue;
                }

                $session = $pr->sessions[0];

                if ($session->team === BZTeamType::OBSERVER) {
                    continue;
                }

                $this->teamScores[$session->team] = 0;
            }
        }
    }

    private function handleMessages(Replay $replay): void
    {
        $messages = $this->em->getRepository(ChatMessage::class)->findPublicChatMessages($replay);

        foreach ($messages as $message) {
            // Ignore slash commands sent to public chat
            if (substr($message->getMessage(), 0, 1) === '/') {
                continue;
            }

            $sender = $message->getSender();
            $senderId = $sender ? $sender->getId() : -1;

            $record = new SummaryChatMessage();
            $record->sender = $senderId;
            $record->senderTeam = $this->playerRecords[$senderId]->team;
            $record->recipient = $message->getRecipient();
            $record->message = $message->getMessage();
            $record->matchTime = $this->calculateMatchTime($message);
            $record->timestamp = $message->getTimestamp();

            $this->chatMessages[] = $record;
        }
    }

    private function handleTeamLoyalty(Replay $replay): void
    {
        $teamLoyalty = new DefaultArray(function () {
            return new DefaultArray(0);
        });

        foreach ($this->playerRecords as $id => $record) {
            foreach ($record->sessions as $session) {
                if ($session->team === BZTeamType::OBSERVER) {
                    continue;
                }

                if ($session->totalTime === null) {
                    $teamLoyalty[$id][$session->team] += $replay->getEndTime()->getTimestamp() - $session->joinTime->getTimestamp();
                } else {
                    $teamLoyalty[$id][$session->team] += $session->totalTime;
                }
            }
        }

        foreach ($this->playerRecords as $id => $record) {
            $record->team = $this->reduceMax($teamLoyalty[$id]) ?? BZTeamType::OBSERVER;
            $record->teamLiteral = BZTeamType::toString($record->team);
        }
    }

    private function handleDuration(Replay $replay): void
    {
        $this->duration = (int)ceil($replay->getDuration() / 60);
    }

    /**
     * @param array $arr
     *
     * @return int
     */
    private function reduceMax(iterable $arr): ?int
    {
        $key = null;
        $max = null;
        $firstRun = true;

        foreach ($arr as $idx => $item) {
            if (($max === null && $firstRun) || $arr[$idx] >= $max) {
                $key = $idx;
                $max = $arr[$key];
            }

            $firstRun = false;
        }

        return $key;
    }

    private function calculateMatchTime(IMatchTimeEvent $event): MatchTime
    {
        $secSinceStart = $event->getMatchSeconds();
        $totalDuration = $event->getReplay()->getDuration();

        $seconds = $totalDuration - $secSinceStart;

        return new MatchTime($seconds);
    }

    /**
     * @throws UnsummarizedException
     */
    private function requiresSummary(): void
    {
        if ($this->summarized === self::UNSUMMARIZED) {
            throw new UnsummarizedException('No replay has been summarized; be sure to call summarizeFull() or summarizeQuick() first.');
        }
    }

    /**
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    private function requiresQuickSummary(): void
    {
        $this->requiresSummary();

        if ($this->summarized < self::SUMMARIZED_QUICK) {
            throw new WrongSummarizationException('This methods requires a quick summary; be sure to call summarizeQuick().');
        }
    }

    /**
     * @throws WrongSummarizationException
     * @throws UnsummarizedException
     */
    private function requiresFullSummary(): void
    {
        $this->requiresSummary();

        if ($this->summarized < self::SUMMARIZED_FULL) {
            throw new WrongSummarizationException('This methods requires a full summary; be sure to call summarizeFull().');
        }
    }

    /**
     * Reset the state of this service so it can be reused for other replays.
     */
    private function resetService(): void
    {
        $this->summarized = self::UNSUMMARIZED;
        $this->teamScores = new DefaultArray(0);
        $this->chatMessages = [];
        $this->flagCaptures = [];
        $this->playerRecords = [
            -1 => $this->createServerPlayerRecord(),
        ];
    }

    /**
     * Create a mock player record for the SERVER player.
     */
    private function createServerPlayerRecord(): SummaryPlayerRecord
    {
        $serverPlayer = new SummaryPlayerRecord();
        $serverPlayer->callsign = 'SERVER';

        // Create a dummy session so it can be assigned to the Rogue team
        $session = new SummarySession();
        $session->team = BZTeamType::ROGUE;
        $session->totalTime = 1;

        $serverPlayer->sessions[] = $session;

        return $serverPlayer;
    }
}
