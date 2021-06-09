<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class AttributeRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(?string $with = 'names')
    {
        parent::__construct(
            'GET',
            'items/attributes',
            [
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE,
                'with' => $with
            ]
        );
    }
}
