<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClearCacheCommand extends ClearDirectoryCommand
{
    protected static $defaultName = 'cache:clear';

    public function __construct(string $name = null, ?Filesystem $fileSystem = null, ?Finder $finder = null)
    {
        parent::__construct($name, $fileSystem, $finder);

        $this->setDirectory(__DIR__ . '/../../cache');
    }

    protected function configure()
    {
        $this->setDescription('Clears the export cache.')
            ->setHelp('This command empties the "cache" folders contents.');
    }
}
