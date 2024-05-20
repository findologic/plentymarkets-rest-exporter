<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

abstract class Request extends GuzzleRequest
{
    protected array $params = [];
    private int $retryCounter = 1;

    public function __construct(
        string $method,
        string $uri,
        array $params = [],
        array $headers = [],
        ?string $body = null,
        string $version = '1.1'
    ) {
        $this->params = $params;
        $headers['User-Agent'] = 'FINDOLOGIC-REST-Exporter/1.0';

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRetryCounter(): int
    {
        return $this->retryCounter;
    }

    public function incrementRetryCounter(): void
    {
        $this->retryCounter++;
    }

    public function isRetryLimitReached(): bool
    {
        if ($this->retryCounter == 5) {
            return true;
        }

        return false;
    }
}
