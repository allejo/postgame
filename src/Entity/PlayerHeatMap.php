<?php

namespace App\Entity;

use App\Repository\PlayerHeatMapRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerHeatMapRepository::class)
 */
class PlayerHeatMap
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Replay::class, inversedBy="playerHeatMaps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $replay;

    /**
     * @ORM\OneToOne(targetEntity=Player::class, inversedBy="playerHeatMap", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="array")
     */
    private $heatmap = [];

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

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getHeatmap(): ?array
    {
        return $this->heatmap;
    }

    public function setHeatmap(array $heatmap): self
    {
        $this->heatmap = $heatmap;

        return $this;
    }
}
