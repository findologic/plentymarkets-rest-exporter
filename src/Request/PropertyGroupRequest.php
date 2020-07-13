<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class PropertyGroupRequest extends Request implements IterableRequestInterface
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
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
