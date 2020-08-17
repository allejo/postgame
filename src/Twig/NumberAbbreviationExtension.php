<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Twig;

use Symfony\Component\Inflector\Inflector;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

class NumberAbbreviationExtension extends AbstractExtension
{
    public function abbreviateNumber(int $number, int $precision = 1, ?string $noun = null, ?string $content = null): Markup
    {
        if ($number < 1000) {
            return new Markup($number, 'UTF-8');
        }

        $abbr = '';
        $divisor = 1;
        $divisors = [
            1000 ** 1 => 'K',  // Thousand
            1000 ** 2 => 'M',  // Million
            1000 ** 3 => 'B',  // Billion
            1000 ** 4 => 'T',  // Trillion
            1000 ** 5 => 'Qa', // Quadrillion
            1000 ** 6 => 'Qi', // Quintillion
        ];

        // Loop through each $divisor and find the lowest amount that matches
        foreach ($divisors as $divisor => $abbr) {
            if (abs($number) < ($divisor * 1000)) {
                break; // We found a match!
            }
        }

        // We found our match, or there were no matches.
        $value = number_format($number / $divisor, $precision) . $abbr;

        // English setup
        $nounUsed = '';
        if ($noun !== null) {
            $nounUsed = Inflector::pluralize($noun);
        }

        $title = trim(implode(' ', [number_format($number), $nounUsed, $content]));

        return new Markup("<span title=\"{$title}\">{$value}</span>", 'UTF-8');
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('number_abbr', [$this, 'abbreviateNumber']),
        ];
    }
}
