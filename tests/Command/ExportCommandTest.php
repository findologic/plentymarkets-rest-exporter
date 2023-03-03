<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use Exception;
use ReflectionObject;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Component\Console\Application;
use PHPUnit\Framework\MockObject\MockObject;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use Symfony\Component\Console\Tester\CommandTester;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ExportCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\DirectoryAware;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;

class ExportCommandTest extends TestCase
{
    use ResponseHelper;
    use DirectoryAware;

    private Application $application;

    private Exporter|MockObject|null $exportMock;

    private LoggerInterface $logger;

    private ExportCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDirectories([
            Utils::env('EXPORT_DIR'),
            Utils::env('LOG_DIR')
        ]);

        $this->application = new Application();
        $this->command = new ExportCommand();
        $this->logger = new DummyLogger();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteDirectories([
            Utils::env('EXPORT_DIR'),
            Utils::env('LOG_DIR')
        ]);
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
        $expectedExceptionMessage = 'Exception BOOO!!';

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
        $mockHandler = new MockHandler([
            $this->getMockResponse('AccountResponse/response.json')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $clientMock = new Client(['handler' => $handlerStack]);

        $this->exportMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ExportCommand($this->logger, $this->logger, $this->exportMock, $clientMock);

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

    public function testAuthorizationHeadersAreSet(): void
    {
        $exportMock = $this->getMockBuilder(Exporter::class)
        ->disableOriginalConstructor()
        ->getMock();

        $command = new ExportCommand($this->logger, $this->logger, $exportMock);
        $refObject   = new ReflectionObject($command);

        $client = $refObject->getProperty('client');
        $client->setAccessible(true);

        /** @var Client */
        $client = $client->getValue();
        $this->assertArrayHasKey('Authorization', $client->getConfig()['headers']);
    }
}
