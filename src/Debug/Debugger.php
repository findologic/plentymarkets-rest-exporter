<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Debug;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Debugger implements DebuggerInterface
{
    private const
        DEBUG_DIR = __DIR__ . '/../../debug',
        DEBUG_EXTENSION = 'json';

    /** @var string */
    private $debugDir;

    /** @var int */
    private $encodingOptions;

    public function __construct(
        string $debugDir = self::DEBUG_DIR,
        $encodingOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) {
        $this->encodingOptions = $encodingOptions;
        $this->debugDir = $debugDir;
    }

    public function save(RequestInterface $request, ResponseInterface $response): void
    {
        $debugDir = sprintf('%s/%s', $this->getDebugDir(), ltrim($this->getRequestPath($request), '/'));
        $data = $this->getData($request, $response);

        $this->doSave($debugDir, $data);
    }

    private function getDebugDir(): string
    {
        return sprintf('%s/%s', $this->debugDir, date('Y-m-d'));
    }

    private function getData(RequestInterface $request, ResponseInterface $response): string
    {
        $rawResponse = $response->getBody()->__toString();
        $jsonDecodedResponse = json_decode($rawResponse);

        if ($jsonDecodedResponse) {
            $rawResponse = $jsonDecodedResponse;
        }

        return json_encode([
            'request' => [
                'rawUrl' => $request->getUri()->__toString(),
                'url' => [
                    'scheme' => $request->getUri()->getScheme(),
                    'userInfo' => $request->getUri()->getUserInfo(),
                    'host' => $request->getUri()->getHost(),
                    'port' => $request->getUri()->getPort(),
                    'path' => $request->getUri()->getPath(),
                    'query' => $request->getUri()->getQuery(),
                    'fragment' => $request->getUri()->getFragment()
                ],
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
            ],
            'response' => [
                'statusCode' => $response->getStatusCode(),
                'reasonPhrase' => $response->getReasonPhrase(),
                'headers' => $response->getHeaders(),
                'rawResponse' => $rawResponse
            ]
        ], $this->encodingOptions);
    }

    private function getRequestPath(RequestInterface $request): string
    {
        return $request->getUri()->getPath();
    }

    private function doSave(string $path, string $data): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = sprintf('%s/%d.%s', $path, strtotime('now'), self::DEBUG_EXTENSION);
        if (!@file_put_contents($file, $data)) {
            throw new Exception(sprintf('Unable to save debug data to file "%s"', $file));
        }
    }
}
