<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class ItemRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct(
        ?string $with = null,
        ?string $lang = null,
        ?string $name = null,
        ?string $manufacturerId = null,
        ?int $id = 107,
        ?int $flagOne = null,
        ?int $flagTwo = null,
        ?string $updatedBetween = null,
        ?string $variationUpdatedBetween = null,
        ?string $variationRelatedUpdatedBetween = null,
        ?string $or = null
    ) {
        parent::__construct(
            'GET',
            'items',
            [
                'with' => $with,
                'lang' => $lang,
                'name' => $name,
                'manufacturerId' => $manufacturerId,
                'id' => $id,
                'flagOne' => $flagOne,
                'flagTwo' => $flagTwo,
                'updatedBetween' => $updatedBetween,
                'variationUpdatedBetween' => $variationUpdatedBetween,
                'variationRelatedUpdatedBetween' => $variationRelatedUpdatedBetween,
                'or' => $or,
                'page' => $this->page,
                'itemsPerPage' => self::$ITEMS_PER_PAGE
            ]
        );
    }
}
