<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearDataCommand extends Command
{
    private const COMMANDS = [
        'cache:clear',
        'clear:debug',
        'clear:export',
        'clear:logs',
    ];

    protected static $defaultName = 'clear';

    protected function configure()
    {
        $this->setDescription('Clears all export data. Including cache, dumps, export files and logs.')
            ->setHelp('Clears all files in directories cache, debug, export and logs');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        foreach (self::COMMANDS as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $input = new ArrayInput([]);

            $io->writeln(sprintf('Running %s...', $commandName));
            $command->run($input, $output);
        }

        $io->success('All data has been cleared successfully.');
        return Command::SUCCESS;
    }
}
