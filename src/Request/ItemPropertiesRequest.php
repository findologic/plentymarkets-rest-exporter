<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class ItemPropertiesRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(?string $with = null, ?string $updatedAt = null, ?string $groupId = null)
    {
        parent::__construct(
            'GET',
            'items/properties',
            [
                'with' => $with,
                'updatedAt' => $updatedAt,
                'groupId' => $groupId,
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
