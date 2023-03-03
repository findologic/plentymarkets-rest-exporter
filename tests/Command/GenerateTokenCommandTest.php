<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Command;

use ReflectionObject;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Component\Console\Application;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Command\GenerateTokenCommand;

class GenerateTokenCommandTest extends TestCase
{
    use ResponseHelper;

    private Application $application;

    private GenerateTokenCommand $command;

    private Exporter|MockObject $exportMock;

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
        $commandTester->execute([
            'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD'
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $this->assertStringContainsString($expectedToken, $commandTester->getDisplay());
        $this->assertStringContainsString('The token was successfully generated.', $commandTester->getDisplay());
    }

    public function testAuthorizationHeadersAreSetForClient()
    {
        $command = new GenerateTokenCommand();
        $refObject   = new ReflectionObject($command);
        $client = $refObject->getProperty('client')->getValue($command);
        
        $this->assertArrayHasKey('Authorization', $client->getConfig()['headers']);
    }

    private function setUpCommandMocks(): void
    {
        $mockHandler = new MockHandler([
            $this->getMockResponse('LoginResponse/response.json'),
            $this->getMockResponse('WebStoreResponse/response.json')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $clientMock = new Client(['handler' => $handlerStack]);

        $this->exportMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new GenerateTokenCommand($clientMock);

        $this->application->add($this->command);
    }
}
