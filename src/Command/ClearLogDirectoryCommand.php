<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClearLogDirectoryCommand extends ClearDirectoryCommand
{
    protected static $defaultName = 'clear:logs';

    public function __construct(string $name = null, ?Filesystem $fileSystem = null, ?Finder $finder = null)
    {
        parent::__construct($name, $fileSystem, $finder);

        $this->setDirectory(__DIR__ . '/../../logs');
    }

    protected function configure()
    {
        $this->setDescription('Clears the logs in the "logs" directory.')
            ->setHelp('This command empties the "logs" folders contents.');
    }
}
