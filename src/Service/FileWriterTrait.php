<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

trait FileWriterTrait
{
    /** @var null|string */
    protected $targetDirectory;

    public function getFileDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function setFileDirectory(string $directory): self
    {
        $this->targetDirectory = $directory;

        return $this;
    }

    private function writeFile(string $filename, string $content): void
    {
        $this->fs->dumpFile($this->getFilePath($filename), $content);
    }

    private function getFilePath(string $filename): string
    {
        return sprintf('%s/%s/%s', $this->targetDirectory, self::FOLDER_NAME, $filename);
    }
}
