<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Command;

use allejo\bzflag\networking\Packets\PacketInvalidException;
use allejo\bzflag\replays\InvalidReplayException;
use allejo\bzflag\world\InvalidWorldCompression;
use allejo\bzflag\world\InvalidWorldDatabase;
use App\Service\ReplayImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

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
            ->addArgument('file', InputArgument::REQUIRED, 'Replay file or folder of replays to import')
            ->addOption('extension', null, InputOption::VALUE_REQUIRED, 'The extension of replays to load in. This is only used when `file` is a folder.', 'rec')
            ->addOption('after', null, InputOption::VALUE_REQUIRED, 'Only import replays after this date/time string. This value can be anything supported by `strtotime()`', null)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not actually import the replay into the database, just make sure it runs without errors.')
            ->addOption('upgrade', null, InputOption::VALUE_NONE, '(DEPRECATED) If a duplicate replay file is found, keep the replay ID but reimport all other information.')
            ->addOption('redo-analysis', null, InputOption::VALUE_NONE, 'If a duplicate replay file is found, keep the replay ID but reimport all other information.')
            ->addOption('regenerate-assets', null, InputOption::VALUE_NONE, 'If a duplicate replay file is found, regenerate the assets related to a replay (e.g. map thumbnails, player density maps, etc.)')
            ->addOption('filenames', null, InputOption::VALUE_REQUIRED, 'A comma-separated list of file names or the path to a text file of file names (separated by new lines) to import from the directory', null)
            ->setDescription('Import a replay file or a folder of replay files')
            ->setHelp('This command allows you to import replay files into the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $redoAnalysis = $input->getOption('redo-analysis');
        $regenAssets = $input->getOption('regenerate-assets');
        $replayFilePath = $input->getArgument('file');

        // @TODO 1.1.0 Remove the deprecated --upgrade option
        if (($doUpgrade = $input->getOption('upgrade')) === true) {
            $output->writeln('[DEPRECATED] The --upgrade flag has been deprecated. Please use `--redo-analysis` instead.');
            $redoAnalysis = $doUpgrade;
        }

        if ($dryRun) {
            $output->writeln('This command is running in "dry mode" meaning nothing will be persisted to the database');
        }

        if ($redoAnalysis) {
            $output->writeln('This command will upgrade existing Replays by re-importing their data');
        }

        if ($regenAssets) {
            $output->writeln('This command will regenerate all of the assets related to a Replay');
        }

        if (!file_exists($replayFilePath)) {
            $output->writeln(sprintf('No such file or directory: %s', $replayFilePath));

            return 5;
        }

        $isDir = is_dir($replayFilePath);

        if (!$isDir) {
            $output->writeln(sprintf('Reading replay file: %s', $replayFilePath));

            try {
                $this->replayService->importReplay($replayFilePath, $dryRun, $redoAnalysis, $regenAssets);
                $output->writeln('Finished.');
            } catch (InvalidWorldCompression | InvalidWorldDatabase | InvalidReplayException | PacketInvalidException $e) {
                $output->writeln(sprintf('An invalid or corrupted replay file was given (%s).', $replayFilePath));
                $output->writeln(sprintf('  %s', $e->getMessage()));

                return 3;
            } catch (\InvalidArgumentException $e) {
                $output->writeln(sprintf('An invalid filepath was given (%s)', $replayFilePath));
                $output->writeln(sprintf('  %s', $e->getMessage()));

                return 4;
            }
        } else {
            $afterTs = $input->getOption('after');

            if ($afterTs !== null && strtotime($afterTs) === false) {
                $output->writeln("The --after flag does not support the following date/time string: $afterTs");

                return 1;
            }

            $output->writeln(sprintf('Reading replay directory: %s', $replayFilePath));

            $replayExtension = $input->getOption('extension');
            $filePattern = sprintf('*.%s', $replayExtension);
            $explicitFiles = $input->getOption('filenames');

            if ($explicitFiles !== null) {
                if (file_exists($explicitFiles)) {
                    $fileNames = @file_get_contents($explicitFiles);

                    if ($fileNames === false) {
                        $output->writeln("The following file could not be read: $explicitFiles");

                        return 2;
                    }

                    $filePattern = explode("\n", $fileNames);
                } else {
                    $filePattern = explode(',', $explicitFiles);
                }
            }

            $replayFiles = new Finder();
            $replayFiles
                ->in($replayFilePath)
                ->name($filePattern)
                ->files()
            ;

            if ($afterTs !== null) {
                $replayFiles->date(">= $afterTs");
            }

            $modifiedCount = 0;
            $errorExit = false;

            ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] -- %message% %filename%');

            $progressBar = new ProgressBar($output, $replayFiles->count());
            $progressBar->setFormat('custom');
            $progressBar->setMessage('Starting...');
            $progressBar->setMessage('', 'filename');
            $progressBar->start();

            foreach ($replayFiles as $replay) {
                $replayFile = $replay->getRealPath();

                try {
                    $progressBar->setMessage('Importing replay...');
                    $progressBar->setMessage(sprintf('(%s)', basename($replayFile)), 'filename');

                    $didImport = $this->replayService->importReplay($replayFile, $dryRun, $redoAnalysis, $regenAssets);

                    if ($didImport) {
                        ++$modifiedCount;
                    }
                } catch (InvalidReplayException | PacketInvalidException $e) {
                    $output->writeln(sprintf('An invalid or corrupted replay file was given (%s).', $replayFile));
                    $output->writeln(sprintf('  %s', $e->getMessage()));
                } catch (\InvalidArgumentException $e) {
                    $output->writeln(sprintf('An invalid filepath was given (%s)', $replayFile));
                    $output->writeln(sprintf('  %s', $e->getMessage()));
                } catch (\Throwable $e) {
                    $output->writeln('');
                    $output->writeln(sprintf('An unknown exception occurred with the given replay (%s)', $replayFile));
                    $output->writeln(sprintf('  %s: %s', get_class($e), $e->getMessage()));

                    $errorExit = true;
                    break;
                }

                $progressBar->advance();
            }

            if (!$errorExit) {
                $progressBar->setMessage('Done!');
                $progressBar->setMessage('', 'filename');
                $progressBar->finish();

                $output->writeln('');
                $output->writeln(sprintf('%d new replays were imported/upgraded.', $modifiedCount));
                $output->writeln('Finished.');
            }
        }

        return 0;
    }
}
