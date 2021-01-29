<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ExportCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExportCommandTest extends TestCase
{
    use ResponseHelper;

    private $application;

    /** @var Exporter|MockObject|null */
    private $exportMock;

    /** @var MockHandler */
    private $mockHandler;

    /** @var Client|null */
    private $clientMock;

    /** @var LoggerInterface */
    private $logger;

    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        if (!is_dir(Utils::env('EXPORT_DIR'))) {
            mkdir(Utils::env('EXPORT_DIR'));
        }
        if (!is_dir(Utils::env('LOG_DIR'))) {
            mkdir(Utils::env('LOG_DIR'));
        }

        $this->application = new Application();
        $this->command = new ExportCommand();
        $this->logger = new DummyLogger();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // PHP is really bad at deleting files recursively. Therefore we go the system approach.
        if (is_dir(Utils::env('EXPORT_DIR'))) {
            exec(sprintf('rm -rf "%s"', Utils::env('EXPORT_DIR')));
        }
        if (is_dir(Utils::env('LOG_DIR'))) {
            exec(sprintf('rm -rf "%s"', Utils::env('LOG_DIR')));
        }
    }

    public function testCommandFailsWhenUsingWrongFormattedShopkey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given shopkey does not match the shopkey format.');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'shopkey' => 'tHiS iS a WrOnG fOrMaTtEd ShOpKeY'
        ]);
    }

    public function testExportDoesNotStartWhenFileAlreadyExists(): void
    {
        $this->createTestLog();
        $this->command = new ExportCommand();
        $this->createTestCsv();
        $this->setUpCommandMocks();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD'
        ]);

        $this->assertStringNotContainsString('Export finished successfully', $commandTester->getDisplay());
        $this->assertStringContainsString(
            'Export has not been started. Files will not be deleted.',
            $commandTester->getDisplay()
        );
    }

    public function testExportStartsWhenForcingDeletionOfOldFile(): void
    {
        $this->setUpCommandMocks();
        $this->createTestCsv();

        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD'
        ]);

        $this->assertStringContainsString('Export finished successfully', $commandTester->getDisplay());
    }

    public function testExportStartsWhenForcingDeletionOfOldFileViaOption(): void
    {
        $this->setUpCommandMocks();
        $this->createTestCsv();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD',
            '--ignore-export-warning' => true,
        ]);

        $this->assertStringContainsString('Export finished successfully', $commandTester->getDisplay());
    }

    public function testExportStopsOnExceptionAndPrintsExceptionDetails(): void
    {
        $expectedExceptionMessage = 'Oops, that shouldn\'t have happened.';

        $this->setUpCommandMocks();

        $this->exportMock->expects($this->once())->method('export')
            ->willThrowException(new Exception($expectedExceptionMessage));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD'
        ]);

        $this->assertStringContainsString('Something went wrong.', $commandTester->getDisplay());
        $this->assertStringContainsString($expectedExceptionMessage, $commandTester->getDisplay());
    }

    private function setUpCommandMocks(): void
    {
        $this->mockHandler = new MockHandler([
            $this->getMockResponse('CustomerLoginResponse/response.json')
        ]);

        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->clientMock = new Client(['handler' => $handlerStack]);

        $this->exportMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ExportCommand($this->logger, $this->logger, $this->exportMock, $this->clientMock);

        $this->application->add($this->command);
    }

    private function createTestCsv(string $data = 'important data'): void
    {
        file_put_contents(Utils::env('EXPORT_DIR') . '/findologic.csv', $data);
    }

    private function createTestLog(string $data = 'This is a logline'): void
    {
        file_put_contents(Utils::env('LOG_DIR') . '/import.log', $data);
    }
}
