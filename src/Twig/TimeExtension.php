<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Twig;

use App\Utility\DateTimeFormatTranslator;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
    public function buildTimeTag(\DateTime $dateTime, string $timeFormat)
    {
        $dayJsFormat = DateTimeFormatTranslator::toDayJS($timeFormat);
        $markup = <<<MARKUP
            <time
                datetime="{$dateTime->format('c')}"
                data-format="{$dayJsFormat}"
                title="{$dateTime->format('F d, Y h:ia T')}"
            >
                {$dateTime->format($timeFormat)} UTC
            </time>
MARKUP;

        return new Markup($markup, 'UTF-8');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('human_time', [$this, 'buildTimeTag']),
        ];
    }
}
