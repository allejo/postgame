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
 * @ORM\Entity(repositoryClass="App\Repository\CaptureEventRepository")
 */
class CaptureEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="captureEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="captureEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $capper;

    /**
     * @ORM\Column(type="integer")
     */
    private $capperTeam;

    /**
     * @ORM\Column(type="integer")
     */
    private $cappedTeam;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

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

    public function getCapper(): ?Player
    {
        return $this->capper;
    }

    public function setCapper(?Player $capper): self
    {
        $this->capper = $capper;

        return $this;
    }

    public function getCapperTeam(): ?int
    {
        return $this->capperTeam;
    }

    public function setCapperTeam(int $capperTeam): self
    {
        $this->capperTeam = $capperTeam;

        return $this;
    }

    public function getCappedTeam(): ?int
    {
        return $this->cappedTeam;
    }

    public function setCappedTeam(int $cappedTeam): self
    {
        $this->cappedTeam = $cappedTeam;

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
}
