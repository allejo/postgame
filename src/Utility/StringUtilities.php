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
    public static function slug(string $str): string
    {
        $str = strtolower($str);
        $str = preg_replace('/\W/', '-', $str);
        $str = preg_replace('/--+/', '-', $str);

        return $str;
    }

    /**
     * Make a string with new lines and multiple spaces all into one line to fit
     * perfectly into a <title> tag.
     *
     * @param string $str
     *
     * @return string
     */
    public static function titlize(string $str): string
    {
        $str = preg_replace('/\n/', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);

        return trim($str);
    }
}
