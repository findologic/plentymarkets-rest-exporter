<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClearDebugDirectoryCommand extends ClearDirectoryCommand
{
    protected static $defaultName = 'clear:debug';

    public function __construct(string $name = null, ?Filesystem $fileSystem = null, ?Finder $finder = null)
    {
        parent::__construct($name, $fileSystem, $finder);

        $this->setDirectory(__DIR__ . '/../../debug');
    }

    protected function configure()
    {
        $this->setDescription('Clears the current export data in the "debug" directory.')
            ->setHelp('This command empties the "debug" folders contents.');
    }
}
