<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Debug;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DummyDebugger implements DebuggerInterface
{
    /**
     * @codeCoverageIgnore Dummy implementation does not need coverage.
     */
    public function save(RequestInterface $request, ResponseInterface $response): void
    {
        // Dummy implementation.
    }
}
