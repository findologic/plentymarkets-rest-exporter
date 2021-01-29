<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClearCacheCommand extends ClearDirectoryBaseCommand
{
    protected static $defaultName = 'cache:clear';

    public function __construct(string $name = null, ?Filesystem $fileSystem = null, ?Finder $finder = null)
    {
        parent::__construct($name, $fileSystem, $finder);

        $this->setDirectory(Utils::env('CACHE_DIR', __DIR__ . '/../../var/cache'));
    }

    protected function configure()
    {
        $this->setDescription('Clears the export cache.')
            ->setHelp('This command empties the "cache" folders contents.');
    }
}
