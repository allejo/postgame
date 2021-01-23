<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

interface IFileWriter
{
    public function getFileDirectory(): string;

    /**
     * @required
     * @param string $directory
     */
    public function setFileDirectory(string $directory);
}
