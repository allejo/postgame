<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Command;

use allejo\bzflag\networking\Packets\PacketInvalidException;
use App\Service\ReplayImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportReplayCommand extends Command
{
    protected static $defaultName = 'app:replay:import';

    /** @var ReplayImportService */
    private $replayService;

    public function __construct(ReplayImportService $replayService)
    {
        parent::__construct();

        $this->replayService = $replayService;
    }

    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Replay file to import')
            ->addOption('extension', null, InputOption::VALUE_REQUIRED, 'The extension of replays to load in.', 'rec')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not actually import the replay into the database, just make sure it runs without errors.')
            ->setDescription('Import a replay file')
            ->setHelp('This command allows you to import a replay file into the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $replayFile = $input->getArgument('file');

        if ($dryRun) {
            $output->writeln('This command is running in "dry mode" meaning nothing will be persisted to the database');
        }

        $isDir = is_dir($replayFile);

        if (!$isDir) {
            $output->writeln(sprintf('Reading replay file: %s', $replayFile));

            try {
                $this->replayService->importReplay($replayFile, $dryRun);
                $output->writeln(sprintf('Finished.'));
            } catch (PacketInvalidException $e) {
                $output->writeln(sprintf('An invalid or corrupted replay file was given (%s).', $replayFile));
                $output->writeln(sprintf('  %s', $e->getMessage()));
            } catch (\InvalidArgumentException $e) {
                $output->writeln(sprintf('An invalid filepath was given (%s)', $replayFile));
                $output->writeln(sprintf('  %s', $e->getMessage()));
            }
        }
    }
}
