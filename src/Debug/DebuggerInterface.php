<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Debug;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface DebuggerInterface
{
    /**
     * Saves the request and response to a debug directory for later lookup/debugging.
     */
    public function save(RequestInterface $request, ResponseInterface $response): void;
}
