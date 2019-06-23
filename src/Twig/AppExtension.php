<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Twig;

use App\Utility\BZTeamType;
use App\Utility\DateTimeFormatTranslator;
use App\Utility\StringUtilities;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('dayjs_fmt', [DateTimeFormatTranslator::class, 'toDayJS']),
            new TwigFilter('slug', [StringUtilities::class, 'slug']),
            new TwigFilter('team_literal', [BZTeamType::class, 'toString']),
        ];
    }
}
