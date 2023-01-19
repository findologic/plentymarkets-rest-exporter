<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class ItemVariationRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct('GET', 'items/variations');
    }

    /**
     * @param string[]|null $with
     */
    public function setWith(?array $with): ItemVariationRequest
    {
        $this->params['with'] = $with;
        return $this;
    }

    public function setIsActive(?bool $isActive): ItemVariationRequest
    {
        $this->params['isActive'] = $isActive;
        return $this;
    }

    public function setLang(?string $lang): ItemVariationRequest
    {
        $this->params['lang'] = $lang;
        return $this;
    }

    /**
     * @param int[]|null $id
     */
    public function setId(?array $id): ItemVariationRequest
    {
        $this->params['id'] = $id;
        return $this;
    }

    /**
     * @param int[]|null $itemId
     */
    public function setItemId(?array $itemId): ItemVariationRequest
    {
        $this->params['itemId'] = $itemId;
        return $this;
    }

    /**
     * @param int[]|null $variationTagId
     */
    public function setVariationTagId(?array $variationTagId): ItemVariationRequest
    {
        $this->params['variationTagId'] = $variationTagId;
        return $this;
    }

    public function setItemName(?string $itemName): ItemVariationRequest
    {
        $this->params['itemName'] = $itemName;
        return $this;
    }

    public function setFlagOne(?string $flagOne): ItemVariationRequest
    {
        $this->params['flagOne'] = $flagOne;
        return $this;
    }

    public function setFlagTwo(?string $flagTwo): ItemVariationRequest
    {
        $this->params['flagTwo'] = $flagTwo;
        return $this;
    }

    public function setStoreSpecial(?int $storeSpecial): ItemVariationRequest
    {
        $this->params['storeSpecial'] = $storeSpecial;
        return $this;
    }

    public function setCategoryId(?int $categoryId): ItemVariationRequest
    {
        $this->params['categoryId'] = $categoryId;
        return $this;
    }

    public function setIsMain(?bool $isMain): ItemVariationRequest
    {
        $this->params['isMain'] = $isMain;
        return $this;
    }

    public function setBarcode(?string $barcode): ItemVariationRequest
    {
        $this->params['barcode'] = $barcode;
        return $this;
    }

    public function setNumberExact(?string $numberExact): ItemVariationRequest
    {
        $this->params['numberExact'] = $numberExact;
        return $this;
    }

    public function setNumberFuzzy(?string $numberFuzzy): ItemVariationRequest
    {
        $this->params['numberFuzzy'] = $numberFuzzy;
        return $this;
    }

    public function setIsBundle(?bool $isBundle): ItemVariationRequest
    {
        $this->params['isBundle'] = $isBundle;
        return $this;
    }

    public function setPlentyId(?int $plentyId): ItemVariationRequest
    {
        $this->params['plentyId'] = $plentyId;
        return $this;
    }

    public function setReferrerId(?int $referrerId): ItemVariationRequest
    {
        $this->params['referrerId'] = $referrerId;
        return $this;
    }

    public function setSupplierNumber(?string $supplierNumber): ItemVariationRequest
    {
        $this->params['supplierNumber'] = $supplierNumber;
        return $this;
    }

    public function setSku(?string $sku): ItemVariationRequest
    {
        $this->params['sku'] = $sku;
        return $this;
    }

    public function setManufacturerId(?int $manufacturerId): ItemVariationRequest
    {
        $this->params['manufacturerId'] = $manufacturerId;
        return $this;
    }

    public function setUpdatedBetween(?string $updatedBetween): ItemVariationRequest
    {
        $this->params['updatedBetween'] = $updatedBetween;
        return $this;
    }

    public function setCreatedBetween(?string $createdBetween): ItemVariationRequest
    {
        $this->params['createdBetween'] = $createdBetween;
        return $this;
    }

    public function setRelatedUpdatedBetween(?string $relatedUpdatedBetween): ItemVariationRequest
    {
        $this->params['relatedUpdatedBetween'] = $relatedUpdatedBetween;
        return $this;
    }

    public function setItemDescription(?string $itemDescription): ItemVariationRequest
    {
        $this->params['itemDescription'] = $itemDescription;
        return $this;
    }

    public function setStockWarehouseId(?string $stockWarehouseId): ItemVariationRequest
    {
        $this->params['stockWarehouseId'] = $stockWarehouseId;
        return $this;
    }

    public function setSupplierId(?int $supplierId): ItemVariationRequest
    {
        $this->params['supplierId'] = $supplierId;
        return $this;
    }
}
