<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequest;

class ManufacturersRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(?string $with = null, ?string $updatedAt = null, ?string $name = null)
    {
        parent::__construct(
            'GET',
            'items/manufacturers',
            [
                'with' => $with,
                'updatedAt' => $updatedAt,
                'name' => $name,
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
