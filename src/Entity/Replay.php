<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReplayRepository")
 */
class Replay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endTime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="replay", orphanRemoval=true)
     */
    private $players;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CaptureEvent", mappedBy="replay", orphanRemoval=true)
     */
    private $captureEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatMessage", mappedBy="replay", orphanRemoval=true)
     */
    private $chatMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FlagUpdate", mappedBy="replay", orphanRemoval=true)
     */
    private $flagUpdates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JoinEvent", mappedBy="replay", orphanRemoval=true)
     */
    private $joinEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KillEvent", mappedBy="replay", orphanRemoval=true)
     */
    private $killEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PartEvent", mappedBy="replay", orphanRemoval=true)
     */
    private $partEvents;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $fileHash;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
     */
    private $canceled;

    /**
     * @ORM\ManyToOne(targetEntity=MapThumbnail::class, inversedBy="replays")
     */
    private $mapThumbnail;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->captureEvents = new ArrayCollection();
        $this->chatMessages = new ArrayCollection();
        $this->flagUpdates = new ArrayCollection();
        $this->joinEvents = new ArrayCollection();
        $this->killEvents = new ArrayCollection();
        $this->partEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setReplay($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            // set the owning side to null (unless already changed)
            if ($player->getReplay() === $this) {
                $player->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CaptureEvent[]
     */
    public function getCaptureEvents(): Collection
    {
        return $this->captureEvents;
    }

    public function addCaptureEvent(CaptureEvent $captureEvent): self
    {
        if (!$this->captureEvents->contains($captureEvent)) {
            $this->captureEvents[] = $captureEvent;
            $captureEvent->setReplay($this);
        }

        return $this;
    }

    public function removeCaptureEvent(CaptureEvent $captureEvent): self
    {
        if ($this->captureEvents->contains($captureEvent)) {
            $this->captureEvents->removeElement($captureEvent);
            // set the owning side to null (unless already changed)
            if ($captureEvent->getReplay() === $this) {
                $captureEvent->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): self
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages[] = $chatMessage;
            $chatMessage->setReplay($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): self
    {
        if ($this->chatMessages->contains($chatMessage)) {
            $this->chatMessages->removeElement($chatMessage);
            // set the owning side to null (unless already changed)
            if ($chatMessage->getReplay() === $this) {
                $chatMessage->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FlagUpdate[]
     */
    public function getFlagUpdates(): Collection
    {
        return $this->flagUpdates;
    }

    public function addFlagUpdate(FlagUpdate $flagUpdate): self
    {
        if (!$this->flagUpdates->contains($flagUpdate)) {
            $this->flagUpdates[] = $flagUpdate;
            $flagUpdate->setReplay($this);
        }

        return $this;
    }

    public function removeFlagUpdate(FlagUpdate $flagUpdate): self
    {
        if ($this->flagUpdates->contains($flagUpdate)) {
            $this->flagUpdates->removeElement($flagUpdate);
            // set the owning side to null (unless already changed)
            if ($flagUpdate->getReplay() === $this) {
                $flagUpdate->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JoinEvent[]
     */
    public function getJoinEvents(): Collection
    {
        return $this->joinEvents;
    }

    public function addJoinEvent(JoinEvent $joinEvent): self
    {
        if (!$this->joinEvents->contains($joinEvent)) {
            $this->joinEvents[] = $joinEvent;
            $joinEvent->setReplay($this);
        }

        return $this;
    }

    public function removeJoinEvent(JoinEvent $joinEvent): self
    {
        if ($this->joinEvents->contains($joinEvent)) {
            $this->joinEvents->removeElement($joinEvent);
            // set the owning side to null (unless already changed)
            if ($joinEvent->getReplay() === $this) {
                $joinEvent->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|KillEvent[]
     */
    public function getKillEvents(): Collection
    {
        return $this->killEvents;
    }

    public function addKillEvent(KillEvent $killEvent): self
    {
        if (!$this->killEvents->contains($killEvent)) {
            $this->killEvents[] = $killEvent;
            $killEvent->setReplay($this);
        }

        return $this;
    }

    public function removeKillEvent(KillEvent $killEvent): self
    {
        if ($this->killEvents->contains($killEvent)) {
            $this->killEvents->removeElement($killEvent);
            // set the owning side to null (unless already changed)
            if ($killEvent->getReplay() === $this) {
                $killEvent->setReplay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PartEvent[]
     */
    public function getPartEvents(): Collection
    {
        return $this->partEvents;
    }

    public function addPartEvent(PartEvent $partEvent): self
    {
        if (!$this->partEvents->contains($partEvent)) {
            $this->partEvents[] = $partEvent;
            $partEvent->setReplay($this);
        }

        return $this;
    }

    public function removePartEvent(PartEvent $partEvent): self
    {
        if ($this->partEvents->contains($partEvent)) {
            $this->partEvents->removeElement($partEvent);
            // set the owning side to null (unless already changed)
            if ($partEvent->getReplay() === $this) {
                $partEvent->setReplay(null);
            }
        }

        return $this;
    }

    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function setFileHash(?string $fileHash): self
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    public function getCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;

        return $this;
    }

    public function getMapThumbnail(): ?MapThumbnail
    {
        return $this->mapThumbnail;
    }

    public function setMapThumbnail(?MapThumbnail $mapThumbnail): self
    {
        $this->mapThumbnail = $mapThumbnail;

        return $this;
    }
}
