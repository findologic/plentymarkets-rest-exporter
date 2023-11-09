<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class CategoryRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(int $storeIdentifier)
    {
        parent::__construct(
            'GET',
            'categories',
            [
                'type' => 'item',
                'with' => ['details'],
                'plentyId' => $storeIdentifier,
                'page' => $this->page,
                'itemsPerPage' => $this->itemsPerPage
            ]
        );
    }
}
