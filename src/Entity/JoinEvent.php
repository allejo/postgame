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
 * @ORM\Entity(repositoryClass="App\Repository\JoinEventRepository")
 */
class JoinEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="joinEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="joinEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $motto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PartEvent", mappedBy="joinEvent", cascade={"persist", "remove"})
     */
    private $partEvent;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getTeam(): ?int
    {
        return $this->team;
    }

    public function setTeam(int $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getMotto(): ?string
    {
        return $this->motto;
    }

    public function setMotto(string $motto): self
    {
        $this->motto = $motto;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

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

    public function getPartEvent(): ?PartEvent
    {
        return $this->partEvent;
    }

    public function setPartEvent(PartEvent $partEvent): self
    {
        $this->partEvent = $partEvent;

        // set the owning side of the relation if necessary
        if ($this !== $partEvent->getJoinEvent()) {
            $partEvent->setJoinEvent($this);
        }

        return $this;
    }
}
