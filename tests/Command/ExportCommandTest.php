<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\ExportCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
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

    private const EXPORT_LOCATION = '/tmp/export-command-test';

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

        if (!is_dir(self::EXPORT_LOCATION)) {
            mkdir(self::EXPORT_LOCATION);
        }

        putenv(sprintf('export_location=%s', self::EXPORT_LOCATION));

        $this->application = new Application();
        $this->command = new ExportCommand();
        $this->logger = new DummyLogger();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir(self::EXPORT_LOCATION)) {
            // PHP is really bad at deleting files recursively. Therefore we go the system approach.
            exec(sprintf('rm -rf "%s"', self::EXPORT_LOCATION));
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
        $this->setUpCommandMocks();
        $this->createTestCsv();

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
        file_put_contents(self::EXPORT_LOCATION . '/findologic.csv', $data);
    }
}
