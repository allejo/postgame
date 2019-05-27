<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KillEventRepository")
 */
class KillEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="killEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="deathEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $victim;

    /**
     * @ORM\Column(type="integer")
     */
    private $victimTeam;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="killEvents")
     */
    private $killer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $killerTeam;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matchSeconds;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReplay(): ?Replay
    {
        return $this->replay;
    }

    public function setReplay(?Replay $replay): self
    {
        $this->replay = $replay;

        return $this;
    }

    public function getVictim(): ?Player
    {
        return $this->victim;
    }

    public function setVictim(?Player $victim): self
    {
        $this->victim = $victim;

        return $this;
    }

    public function getVictimTeam(): ?int
    {
        return $this->victimTeam;
    }

    public function setVictimTeam(int $victimTeam): self
    {
        $this->victimTeam = $victimTeam;

        return $this;
    }

    public function getKiller(): ?Player
    {
        return $this->killer;
    }

    public function setKiller(?Player $killer): self
    {
        $this->killer = $killer;

        return $this;
    }

    public function getKillerTeam(): ?int
    {
        return $this->killerTeam;
    }

    public function setKillerTeam(?int $killerTeam): self
    {
        $this->killerTeam = $killerTeam;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getMatchSeconds(): ?int
    {
        return $this->matchSeconds;
    }

    public function setMatchSeconds(?int $matchSeconds): self
    {
        $this->matchSeconds = $matchSeconds;

        return $this;
    }
}
