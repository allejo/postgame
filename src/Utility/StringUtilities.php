<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class StringUtilities
{
    public static function slug(string $str)
    {
        $str = strtolower($str);
        $str = preg_replace('/\W/', '-', $str);
        $str = preg_replace('/--+/', '-', $str);

        return $str;
    }
}
