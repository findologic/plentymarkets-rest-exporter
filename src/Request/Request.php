<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

abstract class Request extends GuzzleRequest
{
    protected $params = [];

    public function __construct(
        string $method,
        string $uri,
        array $params = [],
        array $headers = [],
        ?string $body = null,
        string $version = '1.1'
    ) {
        $this->params = $params;

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
