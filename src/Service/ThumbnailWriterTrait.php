<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

trait ThumbnailWriterTrait
{
    /** @var null|string */
    protected $targetDirectory;

    public function getThumbnailDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function setThumbnailDirectory(string $mapThumbnails): self
    {
        $this->targetDirectory = $mapThumbnails;

        return $this;
    }
}
