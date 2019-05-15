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
use App\Utility\SummaryCaptureRecord;
use App\Utility\SummaryDeathRecord;
use App\Utility\SummaryKillRecord;
use App\Utility\SummaryPlayerRecord;
use App\Utility\SummarySession;
use Doctrine\ORM\EntityManagerInterface;

class ReplaySummaryService
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Replay $replay
     *
     * @return SummaryPlayerRecord[]
     */
    public function getSummary(Replay $replay): array
    {
        $findByFilter = [
            'replay' => $replay,
        ];

        /** @var SummaryPlayerRecord[] $playersById */
        $playersById = [];

        $this->handlePlayers($playersById, $findByFilter);
        $this->handleKillRecords($playersById, $findByFilter);
        $this->handleJoins($playersById, $findByFilter);
        $this->handleParts($playersById, $findByFilter);
        $this->handleCaps($playersById, $findByFilter);
        $this->handleTeamLoyalty($playersById, $replay);

        return $playersById;
    }

    /**
     * @param SummaryPlayerRecord[] $roster
     * @param array                 $findByFilter
     */
    private function handlePlayers(array &$roster, array $findByFilter): void
    {
        $players = $this->em->getRepository(Player::class)->findBy($findByFilter);

        foreach ($players as $player) {
            $record = new SummaryPlayerRecord();
            $record->callsign = $player->getCallsign();

            $roster[$player->getId()] = $record;
        }
    }

    /**
     * @param SummaryPlayerRecord[] $roster
     * @param array                 $findByFilter
     */
    private function handleKillRecords(array &$roster, array $findByFilter): void
    {
        $kills = $this->em->getRepository(KillEvent::class)->findBy($findByFilter);

        foreach ($kills as $kill) {
            $victimId = $kill->getVictim()->getId();
            $killerId = $kill->getKiller()->getId();

            ++$roster[$victimId]->score->deaths;
            ++$roster[$killerId]->score->kills;

            // This was a team kill
            if ($kill->getVictimTeam() === $kill->getKillerTeam()) {
                ++$roster[$killerId]->score->teamKills;
            }

            $deathRecord = new SummaryDeathRecord();
            $deathRecord->killedBy = $killerId;
            $deathRecord->timestamp = $kill->getTimestamp();

            $roster[$victimId]->deaths[] = $deathRecord;
            ++$roster[$victimId]->against[$killerId]->deaths;

            $killRecord = new SummaryKillRecord();
            $killRecord->victim = $victimId;
            $killRecord->timestamp = $kill->getTimestamp();

            $roster[$killerId]->kills[] = $killRecord;
            ++$roster[$killerId]->against[$victimId]->kills;
        }
    }

    /**
     * @param SummaryPlayerRecord[] $roster
     * @param array                 $findByFilter
     */
    private function handleJoins(array &$roster, array $findByFilter): void
    {
        $joins = $this->em->getRepository(JoinEvent::class)->findBy($findByFilter);

        foreach ($joins as $join) {
            $session = new SummarySession();
            $session->team = $join->getTeam();
            $session->ipAddress = $join->getIpAddress();
            $session->joinTime = $join->getTimestamp();

            $roster[$join->getPlayer()->getId()]->sessions[] = $session;
        }
    }

    /**
     * @param SummaryPlayerRecord[] $roster
     * @param array                 $findByFilter
     */
    private function handleParts(array &$roster, array $findByFilter): void
    {
        $parts = $this->em->getRepository(PartEvent::class)->findBy($findByFilter);

        foreach ($parts as $part) {
            $playerId = $part->getPlayer()->getId();

            foreach ($roster[$playerId]->sessions as &$session) {
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
     * @param SummaryPlayerRecord[] $roster
     * @param array                 $findByFilter
     */
    private function handleCaps(array &$roster, array $findByFilter): void
    {
        $caps = $this->em->getRepository(CaptureEvent::class)->findBy($findByFilter);

        foreach ($caps as $cap) {
            $capperId = $cap->getCapper()->getId();

            $record = new SummaryCaptureRecord();
            $record->playerId = $capperId;
            $record->team = $cap->getCapperTeam();
            $record->timestamp = $cap->getTimestamp();

            $roster[$capperId]->flagCaptures[] = $record;
        }
    }

    /**
     * @param SummaryPlayerRecord[] $roster
     * @param Replay                $replay
     */
    private function handleTeamLoyalty(array &$roster, Replay $replay)
    {
        $teamLoyalty = new DefaultArray(function () {
            return new DefaultArray(0);
        });

        foreach ($roster as $id => $record) {
            foreach ($record->sessions as $session) {
                if ($session->totalTime === null) {
                    $teamLoyalty[$id][$session->team] += $replay->getEndTime()->getTimestamp() - $session->joinTime->getTimestamp();
                } else {
                    $teamLoyalty[$id][$session->team] += $session->totalTime;
                }
            }
        }

        foreach ($roster as $id => $record) {
            $record->team = $this->reduceMax($teamLoyalty[$id]);
            $record->teamLiteral = BZTeamType::toString($record->team);
        }
    }

    /**
     * @param array $arr
     *
     * @return int
     */
    private function reduceMax(iterable $arr): int
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
}
