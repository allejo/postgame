<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

/**
 * A utility class for converting PHP \DateTime formats into equivalent formats for JS formats.
 */
abstract class DateTimeFormatTranslator
{
    /**
     * A mapping of PHP \DateTime format characters to Day.js format characters.
     *
     * @see https://www.php.net/manual/en/function.date.php
     * @see https://github.com/iamkun/dayjs/blob/dev/docs/en/API-reference.md#list-of-all-available-formats
     *
     * @var array<string, string>
     */
    private static $dayJsMapping = [
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'w' => 'd',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        'Y' => 'YYYY',
        'y' => 'YY',
        'G' => 'H',
        'H' => 'HH',
        'g' => 'h',
        'h' => 'hh',
        'i' => 'mm',
        's' => 'ss',
        'v' => 'SSS',
        'P' => 'Z',
        'O' => 'ZZ',
        'a' => 'a',
        'A' => 'A',
    ];

    /**
     * Convert PHP \DateTime format strings into Day.js compatible format strings.
     *
     * @param string $format The \DateTime format by PHP standards
     *
     * @return string The Day.js equivalent
     */
    public static function toDayJS(string $format): string
    {
        $phpCharRegex = '/(?<!\\\\)([a-zA-Z])/';
        $phpEscapedRegex = '/(\\\\[a-zA-Z])/';

        $translated = preg_replace_callback($phpCharRegex, function (array $matches) {
            $char = $matches[1];
            $replacement = self::$dayJsMapping[$char] ?? '';

            // We tried mapping a character that Day.js does not have
            if ($replacement === '') {
                trigger_error(sprintf('The "%s" character does not have an equivalent in Day.js', $char), E_USER_WARNING);
            }

            return $replacement;
        }, $format);

        // Convert PHP date format escaping (\) to Day.js escaping format ([])
        $translated = preg_replace_callback($phpEscapedRegex, function (array $matches) {
            return sprintf('[%s]', substr($matches[1], 1));
        }, $translated);

        // Simplify our Day.js escaping by combining escaped characters that are next to each other
        $translated = str_replace('][', '', $translated);

        return $translated;
    }
}
