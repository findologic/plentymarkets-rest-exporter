<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

class WebStoreRequest extends GuzzleRequest
{
    public function __construct(
        string $method = 'GET',
        string $uri = 'webstores',
        array $headers = [],
        ?string $body = null,
        string $version = '1.1'
    ) {
        parent::__construct($method, $uri, $headers, $body, $version);
    }
}
