<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

interface IThumbnailWriter
{
    public function getThumbnailDirectory(): string;

    /**
     * @required
     */
    public function setThumbnailDirectory(string $map_thumbnails);
}
