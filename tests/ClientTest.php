<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use Carbon\Carbon;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\AuthorizationException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CriticalException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry\EmptyResponseException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\ThrottlingException;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\CategoryRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
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

    private function getDefaultRequest($overrideUri = null, array $params = []): Request
    {
        $uri = $overrideUri ?? sprintf('https://%s/rest/webstores', $this->config->getDomain());

        /** @var Request $request */
        $request = (new WebStoreRequest())->withUri(new Uri($uri))
            ->withAddedHeader('Authorization', 'Bearer access token');

        return $request;
    }

    public function testClientWillAutomaticallyLogin(): void
    {
        $client = $this->getDefaultClient();
        $expectedLoginRequest = new GuzzleRequest('POST', sprintf('https://%s/rest/login', $this->config->getDomain()));
        $expectedWebStoreRequest = $this->getDefaultRequest();

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

    public function badResponseProvider(): array
    {
        $requestUri = 'https://plenty-testshop.de/rest/categories';

        return [
            'response with status code 401' => [
                'request' => new CategoryRequest(1234),
                'response' => new GuzzleResponse(401, [], 'OOF you are not logged in bro...'),
                'expectedException' => AuthorizationException::class,
                'expectedExceptionMessage' => 'The REST client is not logged in.',
            ],
            'response with status code 403' => [
                'request' => new CategoryRequest(1234),
                'response' => new GuzzleResponse(403, [], 'OOF you are not authorized bro...'),
                'expectedException' => CustomerException::class,
                'expectedExceptionMessage' => sprintf(
                    'The REST client does not have access rights for method with URI "%s"',
                    $requestUri
                ),
            ],
            'response with status code 429' => [
                'request' => new CategoryRequest(1234),
                'response' => new GuzzleResponse(429, [], 'You have reached your rate limit :('),
                'expectedException' => ThrottlingException::class,
                'expectedExceptionMessage' => 'Throttling limit reached.',
            ],
            'response with status code 200 but empty response' => [
                'request' => new CategoryRequest(1234),
                'response' => new GuzzleResponse(200, [], ''),
                'expectedException' => EmptyResponseException::class,
                'expectedExceptionMessage' => sprintf(
                    'The API for URI "%s" responded with an empty response',
                    $requestUri
                ),
            ],
            'response with unknown status code 400' => [
                'request' => new CategoryRequest(1234),
                'response' => new GuzzleResponse(400, [], 'Unprocessable entity!'),
                'expectedException' => CustomerException::class,
                'expectedExceptionMessage' => sprintf(
                    'Could not reach API method with URI "%s". Status code was 400.',
                    $requestUri
                ),
            ],
        ];
    }

    /**
     * @dataProvider badResponseProvider
     */
    public function testExceptionsAreThrownIfResponseHasBadStatusCodes(
        Request $request,
        GuzzleResponse $response,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $client = $this->getDefaultClient();

        $this->guzzleClientMock->expects($this->exactly(2))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $this->getMockResponse('LoginResponse/response.json'),
                $response
            );

        $client->send($request);
    }

    public function testClientSwitchesToHttpIfLoginReturnsARedirectResponse(): void
    {
        $client = $this->getDefaultClient();
        $expectedLoginRequest = new GuzzleRequest('POST', sprintf('https://%s/rest/login', $this->config->getDomain()));
        $expectedSecondLoginRequest = new GuzzleRequest(
            'POST',
            sprintf('http://%s/rest/login', $this->config->getDomain())
        );
        $expectedWebStoreRequest = $this->getDefaultRequest(sprintf(
            'http://%s/rest/webstores',
            $this->config->getDomain()
        ));

        $this->guzzleClientMock->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive([$expectedLoginRequest], [$expectedSecondLoginRequest], [$expectedWebStoreRequest])
            ->willReturnOnConsecutiveCalls(
                new Response(301),
                $this->getMockResponse('LoginResponse/response.json'),
                $this->getMockResponse('WebStoreResponse/response.json')
            );

        $request = new WebStoreRequest();

        $client->send($request);
    }

    public function invalidLoginResponseProvider(): array
    {
        $loginRoute = 'https://plenty-testshop.de/rest/login';

        return [
            'response with status code 500' => [
                'response' => new Response(500),
                'expectedException' => CriticalException::class,
                'expectedExceptionMessage' => sprintf(
                    'Unable to connect to the REST API via "%s".',
                    $loginRoute
                )
            ],
            'response with status code 200 but no json' => [
                'response' => new Response(200, [], 'That ain\'t json'),
                'expectedException' => CriticalException::class,
                'expectedExceptionMessage' =>
                    'Wrong username or password. The response does not contain an access token.'
            ],
            'response with status code 200 but empty response' => [
                'response' => new Response(),
                'expectedException' => CriticalException::class,
                'expectedExceptionMessage' =>
                    'Wrong username or password. The response does not contain an access token.'
            ]
        ];
    }

    /**
     * @dataProvider invalidLoginResponseProvider
     */
    public function testInvalidLoginResponseThrowsException(
        Response $response,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $client = $this->getDefaultClient();
        $expectedLoginRequest = new GuzzleRequest('POST', sprintf('https://%s/rest/login', $this->config->getDomain()));

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($expectedLoginRequest)
            ->willReturn($response);

        $request = new WebStoreRequest();

        $client->send($request);
    }

    public function testRateLimitIsHandled(): void
    {
        $client = $this->getDefaultClient();
        $expectedLoginRequest = new GuzzleRequest('POST', sprintf('https://%s/rest/login', $this->config->getDomain()));
        $expectedWebStoreRequest = $this->getDefaultRequest();

        $this->guzzleClientMock->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive([$expectedLoginRequest], [$expectedWebStoreRequest], [$expectedWebStoreRequest])
            ->willReturnOnConsecutiveCalls(
                $this->getMockResponse('LoginResponse/response.json'),
                $this->getMockResponse('WebStoreResponse/response.json')
                    ->withAddedHeader('X-Plenty-Global-Short-Period-Calls-Left', 1)
                    ->withAddedHeader('X-Plenty-Global-Short-Period-Decay', 5),
                $this->getMockResponse('WebStoreResponse/response.json')
            );

        $request = new WebStoreRequest();
        $client->send($request);
        $expectedRateLimitedTime = Carbon::now()->addSeconds(5);
        $client->send($request);

        $this->assertEqualsWithDelta($expectedRateLimitedTime->timestamp, Carbon::now()->timestamp, 2);
    }
}
