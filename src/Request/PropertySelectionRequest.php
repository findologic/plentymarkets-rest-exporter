<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequest;

class PropertySelectionRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct(
            'GET',
            'properties/selections',
            [
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
