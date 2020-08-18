<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Tests\Utility;

use App\Utility\DateTimeFormatTranslator;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

class DateTimeFormatTranslatorTest extends TestCase
{
    public static function dataProvider_testToDayJSConversion(): array
    {
        return [
            ['F j, Y, g:i a', 'MMMM D, YYYY, h:mm a'],
            ['d/m/Y', 'DD/MM/YYYY'],
            ['\Y\Y\Y\Y Y', '[YYYY] YYYY'],
            ['\i\t \i\s \t\h\e j \d\a\y.', '[it] [is] [the] D [day].'],
        ];
    }

    /**
     * @dataProvider dataProvider_testToDayJSConversion
     *
     * @param $phpFormat
     * @param $expectedJS
     */
    public function testToDayJSConversion(string $phpFormat, string $expectedJS): void
    {
        $translated = DateTimeFormatTranslator::toDayJS($phpFormat);

        self::assertEquals($expectedJS, $translated);
    }

    public function testToDayJsConversionThrowsWarningWithUnsupportedCharacter(): void
    {
        $this->expectException(Warning::class);

        $format = '\t\h\e jS \d\a\y';
        $translated = DateTimeFormatTranslator::toDayJS($format);

        self::assertEquals('[the] D [day]', $translated);
    }
}
