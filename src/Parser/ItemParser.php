<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ItemParser extends Parser
{
    public static function parse(ResponseInterface $rawResponse): ItemResponse
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $items = [];
        foreach ($response['entries'] as $item) {
            $items[] = new Item($item);
        }

        return new ItemResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $items,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
