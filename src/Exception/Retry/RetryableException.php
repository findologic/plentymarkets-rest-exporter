<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry;

use Exception;

/**
 * Is thrown when a response is not really what we want, but way may be able to do a retry to solve the issue.
 */
abstract class RetryableException extends Exception
{
}
