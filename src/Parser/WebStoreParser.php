<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\WebStoreResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class WebStoreParser extends Parser
{
    /**
     * @param ResponseInterface $rawResponse
     * @return WebStoreResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $rawWebStores = self::unserializeJsonResponse($rawResponse);

        $webStores = [];
        foreach ($rawWebStores as $rawWebStore) {
            $webStores[] = new WebStore($rawWebStore);
        }

        return new WebStoreResponse($webStores);
    }
}
