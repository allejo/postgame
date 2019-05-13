<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PartEventRepository")
 */
class PartEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="partEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="partEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\JoinEvent", inversedBy="partEvent", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $joinEvent;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getJoinEvent(): ?JoinEvent
    {
        return $this->joinEvent;
    }

    public function setJoinEvent(JoinEvent $joinEvent): self
    {
        $this->joinEvent = $joinEvent;

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
