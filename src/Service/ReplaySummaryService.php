<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\CaptureEvent;
use App\Entity\JoinEvent;
use App\Entity\KillEvent;
use App\Entity\PartEvent;
use App\Entity\Player;
use App\Entity\Replay;
use App\Utility\BZTeamType;
use App\Utility\DefaultArray;
use App\Utility\IMatchTimeEvent;
use App\Utility\SummaryCaptureRecord;
use App\Utility\SummaryDeathRecord;
use App\Utility\SummaryKillRecord;
use App\Utility\SummaryPlayerRecord;
use App\Utility\SummarySession;
use App\Utility\UnsummarizedException;
use Doctrine\ORM\EntityManagerInterface;

class ReplaySummaryService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SummaryPlayerRecord[] */
    private $playerRecords;

    /** @var SummaryCaptureRecord[] */
    private $flagCaptures;

    /** @var int[] */
    private $teamScores;

    /** @var bool */
    private $summarized;

    /** @var int */
    private $duration;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->summarized = false;
        $this->teamScores = new DefaultArray(0);
        $this->playerRecords = [];
    }

    /**
     * Get the duration of the replay in minutes.
     *
     * @throws UnsummarizedException
     *
     * @return int
     */
    public function getDuration(): int
    {
        $this->throwUnsummarizedException();

        return $this->duration;
    }

    /**
     * Get a list of all the flag captures that happened in this match.
     *
     * @return SummaryCaptureRecord[]
     */
    public function getFlagCaps(): array
    {
        return $this->flagCaptures;
    }

    /**
     * @see BZTeamType
     *
     * @throws UnsummarizedException
     *
     * @return int
     */
    public function getWinner(): int
    {
        $this->throwUnsummarizedException();

        $winner = array_keys($this->teamScores->getAsArray(), max($this->teamScores->getAsArray()));

        return $winner[0];
    }

    /**
     * @throws UnsummarizedException
     *
     * @return int
     */
    public function getWinnerScore(): int
    {
        return $this->teamScores[$this->getWinner()];
    }

    /**
     * @see BZTeamType
     *
     * @throws UnsummarizedException
     *
     * @return int
     */
    public function getLoser(): int
    {
        $this->throwUnsummarizedException();

        $winner = $this->getWinner();
        $teams = $this->teamScores->getAsArray();
        unset($teams[$winner]);

        return array_keys($teams)[0];
    }

    /**
     * @throws UnsummarizedException
     *
     * @return int
     */
    public function getLoserScore(): int
    {
        return $this->teamScores[$this->getLoser()];
    }

    /**
     * @throws UnsummarizedException
     *
     * @return SummaryPlayerRecord[]
     */
    public function getPlayerRecords(): array
    {
        $this->throwUnsummarizedException();

        return $this->playerRecords;
    }

    /**
     * @param Replay $replay
     */
    public function summarize(Replay $replay): void
    {
        $this->duration = (int)ceil($replay->getDuration() / 60);

        $findByFilter = [
            'replay' => $replay,
        ];

        $this->handlePlayers($findByFilter);
        $this->handleKillRecords($findByFilter);
        $this->handleJoins($findByFilter);
        $this->handleParts($findByFilter);
        $this->handleCaps($findByFilter);
        $this->handleTeamLoyalty($replay);

        $this->summarized = true;
    }

    /**
     * @param array $findByFilter
     */
    private function handlePlayers(array $findByFilter): void
    {
        $players = $this->em->getRepository(Player::class)->findBy($findByFilter);

        foreach ($players as $player) {
            $record = new SummaryPlayerRecord();
            $record->callsign = $player->getCallsign();

            $this->playerRecords[$player->getId()] = $record;
        }
    }

    /**
     * @param array $findByFilter
     */
    private function handleKillRecords(array $findByFilter): void
    {
        $kills = $this->em->getRepository(KillEvent::class)->findBy($findByFilter);

        foreach ($kills as $kill) {
            $victimId = $kill->getVictim()->getId();
            $killerId = $kill->getKiller()->getId();

            ++$this->playerRecords[$victimId]->score->deaths;
            ++$this->playerRecords[$killerId]->score->kills;

            // This was a team kill
            if ($kill->getVictimTeam() === $kill->getKillerTeam()) {
                ++$this->playerRecords[$killerId]->score->teamKills;
            }

            $deathRecord = new SummaryDeathRecord();
            $deathRecord->killedBy = $killerId;
            $deathRecord->timestamp = $kill->getTimestamp();

            $this->playerRecords[$victimId]->deaths[] = $deathRecord;
            ++$this->playerRecords[$victimId]->against[$killerId]->deaths;

            $killRecord = new SummaryKillRecord();
            $killRecord->victim = $victimId;
            $killRecord->timestamp = $kill->getTimestamp();

            $this->playerRecords[$killerId]->kills[] = $killRecord;
            ++$this->playerRecords[$killerId]->against[$victimId]->kills;
        }
    }

    /**
     * @param array $findByFilter
     */
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

    /**
     * @param array $findByFilter
     */
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

    /**
     * @param array $findByFilter
     */
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
    }

    /**
     * @param Replay $replay
     */
    private function handleTeamLoyalty(Replay $replay)
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

    /**
     * @param IMatchTimeEvent $event
     *
     * @return string
     */
    private function calculateMatchTime(IMatchTimeEvent $event)
    {
        $secSinceStart = $event->getMatchSeconds();
        $totalDuration = $event->getReplay()->getDuration();

        $seconds = $totalDuration - $secSinceStart;

        return sprintf('[%d:%d]', (int)($seconds / 60), (int)($seconds % 60));
    }

    /**
     * @throws UnsummarizedException
     */
    private function throwUnsummarizedException(): void
    {
        if (!$this->summarized) {
            throw new UnsummarizedException('No replay has been summarized; be sure to call summarize() first.');
        }
    }
}
