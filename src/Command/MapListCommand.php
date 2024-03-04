<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\KnownMap;
use App\Repository\KnownMapRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MapListCommand extends Command
{
    protected static $defaultName = 'app:map:list';

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $desc = <<<'DESC'
List all of the known maps

In addition to listing the maps, this also displays how many definitions each
map has.
DESC;

        $this
            ->setDescription($desc)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var KnownMapRepository $knownMapRepo */
        $knownMapRepo = $this->entityManager->getRepository(KnownMap::class);
        $maps = $knownMapRepo->findAll();
        $rows = [];

        foreach ($maps as $map) {
            $rows[] = [
                $map->getId(),
                $map->getName(),
                $map->getSlug(),
                0,
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Map Name', 'Slug', 'Count'])
            ->setRows($rows)
        ;
        $table->render();

        return 0;
    }
}
