<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Replay;
use App\Utility\DefaultArray;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MapBadGuessesCommand extends Command
{
    protected static $defaultName = 'app:map:bad-guesses';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RouterInterface */
    private $router;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    protected function configure(): void
    {
        $desc = <<<'DESC'
List maps that have likely been incorrectly assumed to be Ducati.

Ducati maps are usually played on once and then regenerated. However, sometimes
they will be played on 2 or 3 times. Any more than that, is highly unlikely. So
if there are world hashes that have been used more than that and marked as
Ducati, we have incorrectly assumed the map type.
DESC;

        $this
            ->setDescription($desc)
            ->addOption('hostname', null, InputOption::VALUE_REQUIRED, 'The URL hostname used for the current installation of Postgame', 'postgame.allejo.org')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'The amount of duplicate matches occurring to serve as the base line for erroneous guesses', 3)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hostname = $input->getOption('hostname');
        $duplicateCount = $input->getOption('count');

        $context = $this->router->getContext();
        $context->setHost($hostname);

        $badGuesses = $this->entityManager
            ->getRepository(Replay::class)
            ->findBadDucatiGuesses($duplicateCount)
        ;
        $worldHashes = array_column($badGuesses, 'worldHash');
        $replaysByWorldHash = new DefaultArray([]);

        /** @var Replay[] $replays */
        $replays = $this->entityManager
            ->getRepository(Replay::class)
            ->findBy([
                'worldHash' => $worldHashes,
            ])
        ;

        foreach ($replays as $replay) {
            $replaysByWorldHash[$replay->getWorldHash()][] = $replay;
        }

        $rows = [];
        foreach ($badGuesses as ['worldHash' => $hash, 'replayCount' => $count]) {
            $replayURLs = [];

            foreach ($replaysByWorldHash[$hash] as $i => $replay) {
                // Only generate URLs for the first 5 replays
                if ($i > 5) {
                    break;
                }

                $replayURLs[] = $this->router->generate('replay_show', [
                    'id' => $replay->getId(),
                    'filename' => $replay->getFileName(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $rows[] = [$hash, $count, implode("\n", $replayURLs)];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['World Hash', 'Count', 'Replays'])
            ->setRows($rows)
        ;
        $table->render();

        return 0;
    }
}
