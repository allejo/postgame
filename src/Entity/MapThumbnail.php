<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\MapThumbnailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MapThumbnailRepository::class)
 */
class MapThumbnail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $worldHash;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\OneToMany(targetEntity=Replay::class, mappedBy="mapThumbnail")
     */
    private $replays;

    public function __construct()
    {
        $this->replays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorldHash(): ?string
    {
        return $this->worldHash;
    }

    public function setWorldHash(string $worldHash): self
    {
        $this->worldHash = $worldHash;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return Collection|Replay[]
     */
    public function getReplays(): Collection
    {
        return $this->replays;
    }

    public function addReplay(Replay $replay): self
    {
        if (!$this->replays->contains($replay)) {
            $this->replays[] = $replay;
            $replay->setMapThumbnail($this);
        }

        return $this;
    }

    public function removeReplay(Replay $replay): self
    {
        if ($this->replays->contains($replay)) {
            $this->replays->removeElement($replay);
            // set the owning side to null (unless already changed)
            if ($replay->getMapThumbnail() === $this) {
                $replay->setMapThumbnail(null);
            }
        }

        return $this;
    }
}
