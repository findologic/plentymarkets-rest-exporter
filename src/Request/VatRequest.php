<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class VatRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct(
            'GET',
            'vat',
            [
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
