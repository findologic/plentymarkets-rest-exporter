<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use ConfigHelper;
    use ResponseHelper;

    /** @var GuzzleClient|MockObject */
    private $guzzleClientMock;

    /** @var Config */
    private $config;

    public function setUp(): void
    {
        $this->guzzleClientMock = $this->getMockBuilder(GuzzleClient::class)
            ->onlyMethods(['send'])
            ->getMock();
        $this->config = $this->getDefaultConfig();
    }

    private function getDefaultClient(): Client
    {
        return new Client($this->guzzleClientMock, $this->config);
    }

    public function testClientWillAutomaticallyLogin(): void
    {
        $client = $this->getDefaultClient();
        $expectedLoginRequest = new Request('POST', sprintf('https://%s/rest/login', $this->config->getDomain()));
        $expectedWebStoreRequest = (new WebStoreRequest())->withUri(
            new Uri(sprintf('https://%s/rest/webstores', $this->config->getDomain()))
        )->withAddedHeader('Authorization', 'Bearer access token');

        $this->guzzleClientMock->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$expectedLoginRequest], [$expectedWebStoreRequest])
            ->willReturnOnConsecutiveCalls(
                $this->getMockResponse('LoginResponse/response.json'),
                $this->getMockResponse('WebStoreResponse/response.json')
            );

        $request = new WebStoreRequest();

        $client->send($request);
    }
}
