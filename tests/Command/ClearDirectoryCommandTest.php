<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use FINDOLOGIC\PlentyMarketsRestExporter\Command\ClearCacheCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ClearDataCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ClearDebugDirectoryCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ClearExportDirectoryCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ClearLogDirectoryCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\DirectoryAware;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClearDirectoryCommandTest extends TestCase
{
    use DirectoryAware;

    private const ENV_DIRS = [
        'EXPORT_DIR',
        'LOG_DIR',
        'DEBUG_DIR',
        'CACHE_DIR'
    ];

    private Application $application;

    private ClearDataCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
        $this->command = new ClearDataCommand();
        $this->application->addCommands([
            $this->command,
            new ClearCacheCommand(),
            new ClearDebugDirectoryCommand(),
            new ClearLogDirectoryCommand(),
            new ClearExportDirectoryCommand()
        ]);

        $paths = array_map(function ($name) {
            return Utils::env($name);
        }, self::ENV_DIRS);

        $this->createDirectories($paths);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $paths = array_map(function ($name) {
            return Utils::env($name);
        }, self::ENV_DIRS);

        $this->deleteDirectories($paths);
    }

    public function testDirectoriesAreCleared(): void
    {
        $files = [
            Utils::env('EXPORT_DIR') . '/findologic.csv',
            Utils::env('LOG_DIR') . '/import.log',
            Utils::env('DEBUG_DIR') . '/important.json',
            Utils::env('CACHE_DIR') . '/someCacheFormat',
        ];

        foreach ($files as $file) {
            file_put_contents($file, random_bytes(1000));

            $this->assertFileExists($file);
        }

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        foreach ($files as $file) {
            $this->assertFileDoesNotExist($file);
        }
    }
}
