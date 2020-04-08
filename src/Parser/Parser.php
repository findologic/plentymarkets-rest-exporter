<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

abstract class Parser
{
    abstract public static function parse(ResponseInterface $rawResponse): Response;

    protected static function jsonSerializeResponse(ResponseInterface $rawResponse): array
    {
        $response = $rawResponse->getBody()->__toString();
        return json_decode($response, true);
    }
}
