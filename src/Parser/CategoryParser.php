<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class CategoryParser extends Parser
{
    public static function parse(ResponseInterface $rawResponse): CategoryResponse
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $categories = [];
        foreach ($response['entries'] as $category) {
            $categories[] = new Category($category);
        }

        return new CategoryResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $categories,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
