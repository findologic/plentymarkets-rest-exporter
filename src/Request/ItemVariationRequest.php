<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class ItemVariationRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    /**
     * @param int|string|null $id
     * @param int|string|null $itemId
     * @param int|string|null $variationTagId
     */
    public function __construct(
        ?string $with = null,
        ?bool $isActive = null,
        ?string $lang = null,
        $id = null,
        $itemId = null,
        $variationTagId = null,
        ?string $itemName = null,
        ?string $flagOne = null,
        ?string $flagTwo = null,
        ?int $storeSpecial = null,
        ?int $categoryId = null,
        ?bool $isMain = null,
        ?string $barcode = null,
        ?int $numberExact = null,
        ?int $numberFuzzy = null,
        ?bool $isBundle = null,
        ?int $plentyId = null,
        ?int $referrerId = null,
        ?string $supplierNumber = null,
        ?string $sku = null,
        ?int $manufacturerId = null,
        ?string $updatedBetween = null,
        ?string $createdBetween = null,
        ?string $relatedUpdatedBetween = null,
        ?string $itemDescription = null,
        ?string $stockWarehouseId = null,
        ?int $supplierId = null
    ) {
        parent::__construct(
            'GET',
            'items/variations',
            [
                'with' => $with,
                'lang' => $lang,
                'id' => $id,
                'itemId' => $itemId,
                'variationTagId' => $variationTagId,
                'itemName' => $itemName,
                'flagOne' => $flagOne,
                'flagTwo' => $flagTwo,
                'storeSpecial' => $storeSpecial,
                'categoryId' => $categoryId,
                'isMain' => $isMain,
                'isActive' => $isActive,
                'barcode' => $barcode,
                'numberExact' => $numberExact,
                'numberFuzzy' => $numberFuzzy,
                'isBundle' => $isBundle,
                'plentyId' => $plentyId,
                'referrerId' => $referrerId,
                'supplierNumber' => $supplierNumber,
                'sku' => $sku,
                'manufacturerId' => $manufacturerId,
                'updatedBetween' => $updatedBetween,
                'createdBetween' => $createdBetween,
                'relatedUpdatedBetween' => $relatedUpdatedBetween,
                'itemDescription' => $itemDescription,
                'stockWarehouseId' => $stockWarehouseId,
                'supplierId' => $supplierId
            ]
        );
    }
}
