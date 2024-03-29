<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class UnitRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(?string $updatedAt = null)
    {
        parent::__construct(
            'GET',
            'items/units',
            [
                'updatedAt' => $updatedAt,
                'page' => $this->page,
                'itemsPerPage' => $this->itemsPerPage
            ]
        );
    }
}
