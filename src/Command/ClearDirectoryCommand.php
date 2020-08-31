<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class ClearDirectoryCommand extends Command
{
    private $filesystem;
    private $finder;

    /** @var string */
    private $directory;

    public function __construct(string $name = null, ?Filesystem $fileSystem = null, ?Finder $finder = null)
    {
        parent::__construct($name);

        $this->filesystem = $fileSystem ?? new Filesystem();
        $this->finder = $finder ?? new Finder();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $directories = $this->finder->depth('== 0')->directories()->in($this->directory);
        if ($directories->count() === 0) {
            $io->note('Nothing to clear.');

            return Command::SUCCESS;
        }

        foreach ($directories as $directory) {
            $path = $directory->getRealPath();

            if ($this->filesystem->exists($path)) {
                $io->writeln(sprintf(' - Directory %s will be removed...', $path));
                $this->filesystem->remove($path);
            }
        }

        $io->success(sprintf('Directory %s has been successfully cleared.', realpath($this->directory)));

        return Command::SUCCESS;
    }

    protected function setDirectory(string $directory): void
    {
        $this->directory = $directory;
    }
}
