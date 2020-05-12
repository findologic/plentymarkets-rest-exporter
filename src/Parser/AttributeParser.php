<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributeResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class AttributeParser extends Parser
{
    /**
     * @return AttributeResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $attributes = [];
        foreach ($response['entries'] as $attribute) {
            $attributes[] = new Attribute($attribute);
        }

        return new AttributeResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $attributes,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
