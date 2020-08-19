<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Debugger;

use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Debug\Debugger;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DebuggerTest extends TestCase
{
    private const TEST_PATH = '/tmp/plenty-rest-exporter-tests';

    protected function tearDown(): void
    {
        exec(sprintf('rm -rf %s', self::TEST_PATH));
    }

    protected function setUp(): void
    {
        if (!file_exists(self::TEST_PATH)) {
            mkdir(self::TEST_PATH, 0777, true);
        }
    }

    public function testDebugWritesFileAsExpected(): void
    {
        $expectedMethod = 'GET';
        $expectedUri = 'https://blubbergurken.io/';
        $expectedHeaders = [
            'X-SOMETHING-IMPORTANT' => ['really important'],
            'Content-Type' => ['application/json'],
            'Host' => [$expectedUri]
        ];

        $expectedStatusCode = 200;
        $expectedResponseHeaders = [
            'X-PLENTY-RESPONSE-HEADER' => ['wow']
        ];
        $expectedResponseBody = [
            'cool' => 'nice',
            'entries' => [
                'a' => 'b',
                'id' => 1234
            ]
        ];

        $request = new Request(
            $expectedMethod,
            $expectedUri,
            $expectedHeaders
        );
        $response = new Response(
            $expectedStatusCode,
            $expectedResponseHeaders,
            json_encode($expectedResponseBody)
        );

        $debugger = new Debugger(self::TEST_PATH);
        $debugger->save($request, $response);

        $rawData = file_get_contents(sprintf(
            '%s/%s/%s.json',
            self::TEST_PATH,
            date('Y-m-d'),
            strtotime('now')
        ));

        $data = json_decode($rawData, true);

        $this->assertNotNull($rawData);
        $this->assertNotFalse($rawData);

        $this->assertCount(4, $data['request']);
        $this->assertCount(4, $data['response']);

        $this->assertEquals($expectedMethod, $data['request']['method']);
        $this->assertEquals($expectedUri, $data['request']['rawUrl']);
        $this->assertCount(7, $data['request']['url']);
        $this->assertSame('https', $data['request']['url']['scheme']);
        $this->assertSame('', $data['request']['url']['userInfo']);
        $this->assertSame('blubbergurken.io', $data['request']['url']['host']);
        $this->assertNull($data['request']['url']['port']);
        $this->assertSame('/', $data['request']['url']['path']);
        $this->assertSame('', $data['request']['url']['query']);
        $this->assertSame('', $data['request']['url']['fragment']);
        $this->assertEquals($expectedHeaders, $data['request']['headers']);

        $this->assertEquals($expectedStatusCode, $data['response']['statusCode']);
        $this->assertEquals('OK', $data['response']['reasonPhrase']);
        $this->assertEquals($expectedResponseHeaders, $data['response']['headers']);
        $this->assertEquals($expectedResponseBody, $data['response']['rawResponse']);
    }

    public function testResponsesWhichAreNotJsonAreNotEncodedToJson(): void
    {
        $expectedUri = 'https://blubbergurken.io/';
        $expectedResponseBody = 'Just some random string.. (I am awesome!)';

        $request = new Request(
            'GET',
            $expectedUri
        );
        $response = new Response(
            200,
            [],
            $expectedResponseBody
        );

        $debugger = new Debugger(self::TEST_PATH);
        $debugger->save($request, $response);

        $rawData = file_get_contents(sprintf(
            '%s/%s/%s.json',
            self::TEST_PATH,
            date('Y-m-d'),
            strtotime('now')
        ));

        $data = json_decode($rawData, true);

        $this->assertEquals($expectedResponseBody, $data['response']['rawResponse']);
    }

    public function testExceptionIsThrownIfDebugFileCanNotBeCreated(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Unable to save debug data to file "/');

        $requestPath = 'some/path';

        // Remove pre-created directory from setUp.
        rmdir(self::TEST_PATH);

        $dir = sprintf(
            '%s/%s/%s',
            self::TEST_PATH,
            date('Y-m-d'),
            $requestPath
        );

        mkdir($dir, 0700, true);
        chmod($dir, 0500);

        $request = new Request('GET', sprintf('https://blubbergurken.io/%s', $requestPath));
        $response = new Response();

        $debugger = new Debugger(self::TEST_PATH);
        $debugger->save($request, $response);
    }
}
