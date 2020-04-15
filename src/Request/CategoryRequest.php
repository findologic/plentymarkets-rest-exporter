<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class CategoryRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(int $storeIdentifier, array $params = [])
    {
        parent::__construct(
            'GET',
            'categories',
            array_merge([
                'type' => 'item',
                'with' => ['details'],
                'plentyId' => $storeIdentifier,
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ], $params)
        );
    }
}
