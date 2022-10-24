<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use GuzzleHttp\Psr7\Uri;

trait RequestHelper
{
    protected function createUri(string $uri): Uri
    {
        $uri = new Uri($uri);

        // Guzzle calls __toString() internally on URIs, leading to an internal data structure to be populated.
        // Test URIs that are used for exact comparison lack that internal data structure, so calling __toString()
        // manually is necessary to level the playing field.
        $uri->__toString();

        return $uri;
    }
}
