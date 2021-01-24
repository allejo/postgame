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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

class MapCreateCommand extends Command
{
    protected static $defaultName = 'app:map:create';

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
Create a new known map definition

A known map definition is useful for associating map thumbnails with human-
readable names that can be used in searches.
DESC;

        $this
            ->setDescription($desc)
            ->addArgument('mapName', InputArgument::OPTIONAL, 'The human-readable map name used for this map')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mapName = $input->getArgument('mapName');

        if (!$mapName) {
            $question = new Question('Name of the map to create');
            $mapName = $io->askQuestion($question);
        }

        /** @var KnownMapRepository $knownMapRepo */
        $knownMapRepo = $this->entityManager->getRepository(KnownMap::class);
        $slugger = new AsciiSlugger();
        $slug = strtolower($slugger->slug($mapName)->toString());

        $existingMap = $knownMapRepo->findOneBy(compact('slug'));

        if ($existingMap !== null) {
            $io->error(strtr('A map with the name of "{mapName}" already exists.', ['{mapName}' => $mapName]));

            return 1;
        }

        $newMap = new KnownMap();
        $newMap->setName($mapName);
        $newMap->setSlug($slug);

        $this->entityManager->persist($newMap);
        $this->entityManager->flush();

        $io->success(strtr('You have created a new map by the name of "{mapName}".', ['{mapName}' => $mapName]));

        return 0;
    }
}
