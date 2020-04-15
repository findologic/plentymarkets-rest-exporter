<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Logger;

use Psr\Log\LoggerInterface;

/**
 * Dummy Logger that only implements the logger interface.
 */
class DummyLogger implements LoggerInterface
{
    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function emergency($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function alert($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function critical($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function error($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function warning($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function notice($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function info($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function debug($message, array $context = [])
    {
        // Dummy implementation.
    }

    /**
     * @codeCoverageIgnore Logger without implementation needs no tests.
     */
    public function log($level, $message, array $context = [])
    {
        // Dummy implementation.
    }
}
