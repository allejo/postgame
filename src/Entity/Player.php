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
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $callsign;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="players")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $replay;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CaptureEvent", mappedBy="capper", orphanRemoval=true)
     */
    private $captureEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatMessage", mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatMessage", mappedBy="recipient")
     */
    private $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FlagUpdate", mappedBy="player", orphanRemoval=true)
     */
    private $flagUpdates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JoinEvent", mappedBy="player", orphanRemoval=true)
     */
    private $joinEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KillEvent", mappedBy="victim", orphanRemoval=true)
     */
    private $deathEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KillEvent", mappedBy="killer")
     */
    private $killEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PartEvent", mappedBy="player", orphanRemoval=true)
     */
    private $partEvents;

    public function __construct()
    {
        $this->captureEvents = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->flagUpdates = new ArrayCollection();
        $this->joinEvents = new ArrayCollection();
        $this->deathEvents = new ArrayCollection();
        $this->killEvents = new ArrayCollection();
        $this->partEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(string $callsign): self
    {
        $this->callsign = $callsign;

        return $this;
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
            $captureEvent->setCapper($this);
        }

        return $this;
    }

    public function removeCaptureEvent(CaptureEvent $captureEvent): self
    {
        if ($this->captureEvents->contains($captureEvent)) {
            $this->captureEvents->removeElement($captureEvent);
            // set the owning side to null (unless already changed)
            if ($captureEvent->getCapper() === $this) {
                $captureEvent->setCapper(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(ChatMessage $chatMessage): self
    {
        if (!$this->sentMessages->contains($chatMessage)) {
            $this->sentMessages[] = $chatMessage;
            $chatMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(ChatMessage $chatMessage): self
    {
        if ($this->sentMessages->contains($chatMessage)) {
            $this->sentMessages->removeElement($chatMessage);
            // set the owning side to null (unless already changed)
            if ($chatMessage->getSender() === $this) {
                $chatMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(ChatMessage $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setRecipient($this);
        }

        return $this;
    }

    public function removeReceivedMessage(ChatMessage $receivedMessage): self
    {
        if ($this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->removeElement($receivedMessage);
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getRecipient() === $this) {
                $receivedMessage->setRecipient(null);
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
            $flagUpdate->setPlayer($this);
        }

        return $this;
    }

    public function removeFlagUpdate(FlagUpdate $flagUpdate): self
    {
        if ($this->flagUpdates->contains($flagUpdate)) {
            $this->flagUpdates->removeElement($flagUpdate);
            // set the owning side to null (unless already changed)
            if ($flagUpdate->getPlayer() === $this) {
                $flagUpdate->setPlayer(null);
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
            $joinEvent->setPlayer($this);
        }

        return $this;
    }

    public function removeJoinEvent(JoinEvent $joinEvent): self
    {
        if ($this->joinEvents->contains($joinEvent)) {
            $this->joinEvents->removeElement($joinEvent);
            // set the owning side to null (unless already changed)
            if ($joinEvent->getPlayer() === $this) {
                $joinEvent->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|KillEvent[]
     */
    public function getDeathEvents(): Collection
    {
        return $this->deathEvents;
    }

    public function addDeathEvent(KillEvent $deathEvent): self
    {
        if (!$this->deathEvents->contains($deathEvent)) {
            $this->deathEvents[] = $deathEvent;
            $deathEvent->setVictim($this);
        }

        return $this;
    }

    public function removeDeathEvent(KillEvent $deathEvent): self
    {
        if ($this->deathEvents->contains($deathEvent)) {
            $this->deathEvents->removeElement($deathEvent);
            // set the owning side to null (unless already changed)
            if ($deathEvent->getVictim() === $this) {
                $deathEvent->setVictim(null);
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
            $killEvent->setKiller($this);
        }

        return $this;
    }

    public function removeKillEvent(KillEvent $killEvent): self
    {
        if ($this->killEvents->contains($killEvent)) {
            $this->killEvents->removeElement($killEvent);
            // set the owning side to null (unless already changed)
            if ($killEvent->getKiller() === $this) {
                $killEvent->setKiller(null);
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
            $partEvent->setPlayer($this);
        }

        return $this;
    }

    public function removePartEvent(PartEvent $partEvent): self
    {
        if ($this->partEvents->contains($partEvent)) {
            $this->partEvents->removeElement($partEvent);
            // set the owning side to null (unless already changed)
            if ($partEvent->getPlayer() === $this) {
                $partEvent->setPlayer(null);
            }
        }

        return $this;
    }
}
