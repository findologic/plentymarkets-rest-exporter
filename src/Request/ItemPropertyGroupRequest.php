<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class ItemPropertyGroupRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(string $with)
    {
        parent::__construct(
            'GET',
            'items/property_groups',
            [
                'with' => $with,
                'page' => $this->page,
                'itemsPerPage' => $this->itemsPerPage
            ]
        );
    }
}
