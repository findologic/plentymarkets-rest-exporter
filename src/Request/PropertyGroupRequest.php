<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class PropertyGroupRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct(
            'GET',
            'v2/properties/groups',
            [
                'page' => $this->page,
                'itemsPerPage' => $this->itemsPerPage,
                'with' => 'names'
            ]
        );
    }
}
