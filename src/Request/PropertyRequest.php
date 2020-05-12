<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class PropertyRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct(
            'GET',
            'properties',
            [
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
