<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use Psr\Http\Message\ResponseInterface;

class Utils
{
    /**
     * Sends an iterable request. Iterable requests will basically return all data from a specific endpoint.
     * Returns an array of all responses.
     *
     * @param Client $client
     * @param IterableRequestInterface|Request $request
     * @return ResponseInterface[]
     */
    public static function sendIterableRequest(Client $client, IterableRequestInterface $request): array
    {
        $responses = [];
        $lastPage = false;
        while (!$lastPage) {
            $response = $client->send($request);
            $lastPage = self::parseIsLastPage($response);
            $request->setPage($request->getPage() + 1);

            $responses[] = $response;
        }

        return $responses;
    }

    private static function parseIsLastPage(ResponseInterface $response): bool
    {
        return json_decode($response->getBody()->__toString(), true)['isLastPage'];
    }
}
