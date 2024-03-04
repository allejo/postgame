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
use App\Entity\MapThumbnail;
use App\Entity\Replay;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class MapAssociateCommand extends Command
{
    protected static $defaultName = 'app:map:associate';

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Associate a map hash with a known map in the database')
            ->addOption('to-map-slug', null, InputOption::VALUE_OPTIONAL, '')
            ->addOption('to-map-id', null, InputOption::VALUE_OPTIONAL, '')
            ->addOption('from-replay-id', null, InputOption::VALUE_OPTIONAL, '')
            ->addOption('from-hash', null, InputOption::VALUE_OPTIONAL, '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $knownMap = $this->getKnownMap($input, $io);

        $thumbnail = $this->getMapThumbnail($input, $io);
        $thumbnail->setKnownMap($knownMap);

        $this->entityManager->persist($thumbnail);
        $this->entityManager->flush();

        $io->writeln('Done!');

        return 0;
    }

    private function getKnownMap(InputInterface $input, SymfonyStyle $io): KnownMap
    {
        $knownMapRepo = $this->entityManager->getRepository(KnownMap::class);

        if (($knownMapId = $input->getOption('to-map-id')) !== null) {
            $knownMap = $knownMapRepo->find($knownMapId);

            if ($knownMap) {
                return $knownMap;
            }
        }

        if (($mapSlug = $input->getOption('to-map-slug')) !== null) {
            $knownMap = $knownMapRepo->findOneBy(['slug' => $mapSlug]);

            if ($knownMap) {
                return $knownMap;
            }
        }

        $maps = $knownMapRepo->findAll();
        $mapNames = array_map(static function ($map) { return $map->getName(); }, $maps);

        while (true) {
            $helper = new Question('Please enter map name');
            $helper->setAutocompleterValues($mapNames);

            $mapInput = $io->askQuestion($helper);

            if (($knownMap = $knownMapRepo->findOneBy(['name' => $mapInput])) !== null) {
                return $knownMap;
            }
        }
    }

    private function getMapThumbnail(InputInterface $input, SymfonyStyle $io): MapThumbnail
    {
        $replayRepo = $this->entityManager->getRepository(Replay::class);
        $thumbnailRepo = $this->entityManager->getRepository(MapThumbnail::class);

        if (($hashInput = $input->getOption('from-hash')) !== null) {
            $mapThumbnail = $thumbnailRepo->findBy([
                'worldHash' => $hashInput,
            ], null, 1);

            if ($mapThumbnail !== null && count($mapThumbnail) === 1) {
                return $mapThumbnail[0];
            }
        }

        if (($replayID = $input->getOption('from-replay-id')) !== null) {
            $replay = $replayRepo->find($replayID);

            if ($replay && $replay->getMapThumbnail()) {
                return $replay->getMapThumbnail();
            }
        }

        while (true) {
            $hashInput = $io->ask('Please enter world database hash');
            $mapThumbnail = $thumbnailRepo->findBy([
                'worldHash' => $hashInput,
            ], null, 1);

            if ($mapThumbnail !== null && count($mapThumbnail) === 1) {
                return $mapThumbnail[0];
            }
        }
    }
}
