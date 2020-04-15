<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry;

use Throwable;

class EmptyResponseException extends RetryableException
{
    public function __construct(string $uri, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('The API for URI "%s" responded with an empty response', $uri),
            $code,
            $previous
        );
    }
}
