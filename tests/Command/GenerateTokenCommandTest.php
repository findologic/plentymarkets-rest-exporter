<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use FINDOLOGIC\PlentyMarketsRestExporter\Command\ExportCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\GenerateTokenCommand;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateTokenCommandTest extends TestCase
{
    use ResponseHelper;

    /** @var MockHandler */
    private $mockHandler;

    /** @var Client|MockObject */
    private $clientMock;

    /** @var Application */
    private $application;

    /** @var GenerateTokenCommand */
    private $command;

    /** @var Exporter|MockObject */
    private $exportMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
    }

    public function testTokenIsGenerated(): void
    {
        $this->setUpCommandMocks();

        $expectedToken = 'access token';

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $this->assertStringContainsString($expectedToken, $commandTester->getDisplay());
        $this->assertStringContainsString('The token was successfully generated.', $commandTester->getDisplay());
    }

    private function setUpCommandMocks(): void
    {
        $this->mockHandler = new MockHandler([
            $this->getMockResponse('LoginResponse/response.json'),
            $this->getMockResponse('WebStoreResponse/response.json')
        ]);

        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->clientMock = new Client(['handler' => $handlerStack]);

        $this->exportMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new GenerateTokenCommand($this->clientMock);

        $this->application->add($this->command);
    }
}
