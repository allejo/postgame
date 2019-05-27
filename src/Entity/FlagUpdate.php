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
 * @ORM\Entity(repositoryClass="App\Repository\FlagUpdateRepository")
 */
class FlagUpdate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Replay", inversedBy="flagUpdates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="flagUpdates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isGrab;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $flagAbbv;

    /**
     * @ORM\Column(type="float")
     */
    private $posX;

    /**
     * @ORM\Column(type="float")
     */
    private $posY;

    /**
     * @ORM\Column(type="float")
     */
    private $posZ;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getIsGrab(): ?bool
    {
        return $this->isGrab;
    }

    public function setIsGrab(bool $isGrab): self
    {
        $this->isGrab = $isGrab;

        return $this;
    }

    public function getFlagAbbv(): ?string
    {
        return $this->flagAbbv;
    }

    public function setFlagAbbv(string $flagAbbv): self
    {
        $this->flagAbbv = $flagAbbv;

        return $this;
    }

    public function getPosX(): ?float
    {
        return $this->posX;
    }

    public function setPosX(float $posX): self
    {
        $this->posX = $posX;

        return $this;
    }

    public function getPosY(): ?float
    {
        return $this->posY;
    }

    public function setPosY(float $posY): self
    {
        $this->posY = $posY;

        return $this;
    }

    public function getPosZ(): ?float
    {
        return $this->posZ;
    }

    public function setPosZ(float $posZ): self
    {
        $this->posZ = $posZ;

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
