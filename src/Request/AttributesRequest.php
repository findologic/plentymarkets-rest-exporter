<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequest;

class AttributesRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(?string $updatedAt = null)
    {
        parent::__construct(
            'GET',
            'items/attributes',
            [
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
