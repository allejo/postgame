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
 * @ORM\Entity(repositoryClass="App\Repository\ChatMessageRepository")
 */
class ChatMessage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="chatMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="sentMessages")
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="receivedMessages")
     */
    private $recipient;

    /**
     * @ORM\Column(type="integer")
     */
    private $teamFrom;

    /**
     * @ORM\Column(type="integer")
     */
    private $teamTo;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $message;

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

    public function getSender(): ?Player
    {
        return $this->sender;
    }

    public function setSender(?Player $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?Player
    {
        return $this->recipient;
    }

    public function setRecipient(?Player $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getTeamFrom(): ?int
    {
        return $this->teamFrom;
    }

    public function setTeamFrom(int $teamFrom): self
    {
        $this->teamFrom = $teamFrom;

        return $this;
    }

    public function getTeamTo(): ?int
    {
        return $this->teamTo;
    }

    public function setTeamTo(int $teamTo): self
    {
        $this->teamTo = $teamTo;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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
