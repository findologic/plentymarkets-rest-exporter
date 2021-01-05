<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Base extends Entity
{
    /** @var bool */
    private $isMain;

    /** @var int|null */
    private $mainVariationId;

    /** @var int */
    private $itemId;

    /** @var int */
    private $position;

    /** @var bool */
    private $isActive;

    /** @var string */
    private $number;

    /** @var string|null */
    private $model;

    /** @var string */
    private $externalId;

    /** @var int */
    private $availability;

    /** @var DateTimeInterface|null */
    private $estimatedAvailableAt;

    /** @var float */
    private $purchasePrice;

    /** @var float|null */
    private $movingAveragePrice;

    /** @var string|null */
    private $priceCalculationId;

    /** @var null Unknown data. */
    private $picking = null;

    /** @var int */
    private $stockLimitation;

    /** @var bool */
    private $isVisibleIfNetStockIsPositive;

    /** @var bool */
    private $isInvisibleIfNetStockIsNotPositive;

    /** @var bool */
    private $isAvailableIfNetStockIsPositive;

    /** @var bool */
    private $isUnavailableIfNetStockIsNotPositive;

    /** @var bool */
    private $isVisibleInListIfNetStockIsPositive;

    /** @var bool */
    private $isInvisibleInListIfNetStockIsNotPositive;

    /** @var int */
    private $mainWarehouseId;

    /** @var int */
    private $maximumOrderQuantity;

    /** @var int|null */
    private $minimumOrderQuantity;

    /** @var int|null */
    private $intervalOrderQuantity;

    /** @var DateTimeInterface|null */
    private $availableUntil;

    /** @var DateTimeInterface|null */
    private $releasedAt;

    /** @var string|null */
    private $name;

    /** @var int */
    private $weightG;

    /** @var int */
    private $weightNetG;

    /** @var int */
    private $widthMM;

    /** @var int */
    private $lengthMM;

    /** @var int */
    private $heightMM;

    /** @var float */
    private $extraShippingCharge1;

    /** @var float */
    private $extraShippingCharge2;

    /** @var int */
    private $unitsContained;

    /** @var int|null */
    private $palletTypeId;

    /** @var int */
    private $packingUnits;

    /** @var int */
    private $packingUnitTypeId;

    /** @var float */
    private $transportationCosts;

    /** @var float */
    private $storageCosts;

    /** @var int */
    private $customs;

    /** @var int */
    private $operatingCosts;

    /** @var int */
    private $vatId;

    /** @var string|null */
    private $bundleType;

    /** @var int */
    private $automaticClientVisibility;

    /** @var int */
    private $automaticListVisibility;

    /** @var bool */
    private $isHiddenInCategoryList;

    /** @var float|null */
    private $defaultShippingCosts;

    /** @var bool */
    private $mayShowUnitPrice;

    /** @var int|null */
    private $parentVariationId;

    /** @var int|null */
    private $parentVariationQuantity;

    /** @var int|null */
    private $singleItemCount;

    /** @var bool|null */
    private $hasCalculatedBundleWeight;

    /** @var bool|null */
    private $hasCalculatedBundleNetWeight;

    /** @var bool|null */
    private $hasCalculatedBundlePurchasePrice;

    /** @var bool|null */
    private $hasCalculatedBundleMovingAveragePrice;

    /** @var int|null */
    private $customsTariffNumber;

    /** @var bool */
    private $categoriesInherited;

    /** @var bool */
    private $referrerInherited;

    /** @var bool */
    private $clientsInherited;

    /** @var bool */
    private $salesPricesInherited;

    /** @var bool */
    private $supplierInherited;

    /** @var bool */
    private $warehousesInherited;

    /** @var bool */
    private $propertiesInherited;

    /** @var bool */
    private $tagsInherited;

    /** @var BaseItemDetails */
    private $item;

    /** @var Characteristic[] */
    private $characteristics;

    /** @var Image[] */
    private $images;

    public function __construct(array $data)
    {
        $this->isMain = $this->getBoolProperty('isMain', $data);
        $this->mainVariationId = $this->getIntProperty('mainVariationId', $data);
        $this->itemId = $this->getIntProperty('itemId', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->isActive = $this->getBoolProperty('isActive', $data);
        $this->number = $this->getStringProperty('number', $data);
        $this->model = $this->getStringProperty('model', $data);
        $this->externalId = $this->getStringProperty('externalId', $data);
        $this->availability = $this->getIntProperty('availability', $data);
        $this->estimatedAvailableAt = $this->getDateTimeProperty('estimatedAvailableAt', $data);
        $this->purchasePrice = $this->getFloatProperty('purchasePrice', $data);
        $this->movingAveragePrice = $this->getFloatProperty('movingAveragePrice', $data);
        $this->priceCalculationId = $this->getStringProperty('priceCalculationId', $data);
        $this->stockLimitation = $this->getIntProperty('stockLimitation', $data);
        $this->isVisibleIfNetStockIsPositive = $this->getBoolProperty('isVisibleIfNetStockIsPositive', $data);
        $this->isInvisibleIfNetStockIsNotPositive = $this->getBoolProperty('isInvisibleIfNetStockIsNotPositive', $data);
        $this->isAvailableIfNetStockIsPositive = $this->getBoolProperty('isAvailableIfNetStockIsPositive', $data);
        $this->isUnavailableIfNetStockIsNotPositive = $this->getBoolProperty(
            'isUnavailableIfNetStockIsNotPositive',
            $data
        );
        $this->isVisibleInListIfNetStockIsPositive = $this->getBoolProperty(
            'isVisibleInListIfNetStockIsPositive',
            $data
        );
        $this->isInvisibleInListIfNetStockIsNotPositive = $this->getBoolProperty(
            'isInvisibleInListIfNetStockIsNotPositive',
            $data
        );
        $this->mainWarehouseId = $this->getIntProperty('mainWarehouseId', $data);
        $this->maximumOrderQuantity = $this->getIntProperty('maximumOrderQuantity', $data);
        $this->minimumOrderQuantity = $this->getIntProperty('minimumOrderQuantity', $data);
        $this->intervalOrderQuantity = $this->getIntProperty('intervalOrderQuantity', $data);
        $this->availableUntil = $this->getDateTimeProperty('availableUntil', $data);
        $this->releasedAt = $this->getDateTimeProperty('releasedAt', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->weightG = $this->getIntProperty('weightG', $data);
        $this->weightNetG = $this->getIntProperty('weightNetG', $data);
        $this->widthMM = $this->getIntProperty('widthMM', $data);
        $this->lengthMM = $this->getIntProperty('lengthMM', $data);
        $this->heightMM = $this->getIntProperty('heightMM', $data);
        $this->extraShippingCharge1 = $this->getFloatProperty('extraShippingCharge1', $data);
        $this->extraShippingCharge2 = $this->getFloatProperty('extraShippingCharge2', $data);
        $this->unitsContained = $this->getIntProperty('unitsContained', $data);
        $this->palletTypeId = $this->getIntProperty('palletTypeId', $data);
        $this->packingUnits = $this->getIntProperty('packingUnits', $data);
        $this->packingUnitTypeId = $this->getIntProperty('packingUnitTypeId', $data);
        $this->transportationCosts = $this->getFloatProperty('transportationCosts', $data);
        $this->storageCosts = $this->getFloatProperty('storageCosts', $data);
        $this->customs = $this->getIntProperty('customs', $data);
        $this->operatingCosts = $this->getIntProperty('operatingCosts', $data);
        $this->vatId = $this->getIntProperty('vatId', $data);
        $this->bundleType = $this->getStringProperty('bundleType', $data);
        $this->automaticClientVisibility = $this->getIntProperty('automaticClientVisibility', $data);
        $this->automaticListVisibility = $this->getIntProperty('automaticListVisibility', $data);
        $this->isHiddenInCategoryList = $this->getBoolProperty('isHiddenInCategoryList', $data);
        $this->defaultShippingCosts = $this->getFloatProperty('defaultShippingCosts', $data);
        $this->mayShowUnitPrice = $this->getBoolProperty('mayShowUnitPrice', $data);
        $this->parentVariationId = $this->getIntProperty('parentVariationId', $data);
        $this->parentVariationQuantity = $this->getIntProperty('parentVariationQuantity', $data);
        $this->singleItemCount = $this->getIntProperty('singleItemCount', $data);
        $this->hasCalculatedBundleWeight = $this->getBoolProperty('hasCalculatedBundleWeight', $data);
        $this->hasCalculatedBundleNetWeight = $this->getBoolProperty('hasCalculatedBundleNetWeight', $data);
        $this->hasCalculatedBundlePurchasePrice = $this->getBoolProperty('hasCalculatedBundlePurchasePrice', $data);
        $this->hasCalculatedBundleMovingAveragePrice = $this->getBoolProperty(
            'hasCalculatedBundleMovingAveragePrice',
            $data
        );
        $this->customsTariffNumber = $this->getIntProperty('customsTariffNumber', $data);
        $this->categoriesInherited = $this->getBoolProperty('categoriesInherited', $data);
        $this->referrerInherited = $this->getBoolProperty('referrerInherited', $data);
        $this->clientsInherited = $this->getBoolProperty('clientsInherited', $data);
        $this->salesPricesInherited = $this->getBoolProperty('salesPricesInherited', $data);
        $this->supplierInherited = $this->getBoolProperty('supplierInherited', $data);
        $this->warehousesInherited = $this->getBoolProperty('warehousesInherited', $data);
        $this->propertiesInherited = $this->getBoolProperty('propertiesInherited', $data);
        $this->tagsInherited = $this->getBoolProperty('tagsInherited', $data);
        $this->item = $this->getEntity(BaseItemDetails::class, $data['item']);
        $this->characteristics = $this->getEntities(Characteristic::class, 'characteristics', $data);
        $this->images = $this->getEntities(Image::class, 'images', $data);
    }

    public function getData(): array
    {
        return [
            'isMain' => $this->isMain,
            'mainVariationId' => $this->mainVariationId,
            'itemId' => $this->itemId,
            'position' => $this->position,
            'isActive' => $this->isActive,
            'number' => $this->number,
            'model' => $this->model,
            'externalId' => $this->externalId,
            'availability' => $this->availability,
            'estimatedAvailableAt' => $this->estimatedAvailableAt,
            'purchasePrice' => $this->purchasePrice,
            'movingAveragePrice' => $this->movingAveragePrice,
            'priceCalculationId' => $this->priceCalculationId,
            'stockLimitation' => $this->stockLimitation,
            'isVisibleIfNetStockIsPositive' => $this->isVisibleIfNetStockIsPositive,
            'isInvisibleIfNetStockIsNotPositive' => $this->isInvisibleIfNetStockIsNotPositive,
            'isAvailableIfNetStockIsPositive' => $this->isAvailableIfNetStockIsPositive,
            'isUnavailableIfNetStockIsNotPositive' => $this->isUnavailableIfNetStockIsNotPositive,
            'isVisibleInListIfNetStockIsPositive' => $this->isVisibleInListIfNetStockIsPositive,
            'isInvisibleInListIfNetStockIsNotPositive' => $this->isInvisibleInListIfNetStockIsNotPositive,
            'mainWarehouseId' => $this->mainWarehouseId,
            'maximumOrderQuantity' => $this->maximumOrderQuantity,
            'minimumOrderQuantity' => $this->minimumOrderQuantity,
            'intervalOrderQuantity' => $this->intervalOrderQuantity,
            'availableUntil' => $this->availableUntil,
            'releasedAt' => $this->releasedAt,
            'name' => $this->name,
            'picking' => $this->picking,
            'weightG' => $this->weightG,
            'weightNetG' => $this->weightNetG,
            'widthMM' => $this->widthMM,
            'lengthMM' => $this->lengthMM,
            'heightMM' => $this->heightMM,
            'extraShippingCharge1' => $this->extraShippingCharge1,
            'extraShippingCharge2' => $this->extraShippingCharge2,
            'unitsContained' => $this->unitsContained,
            'palletTypeId' => $this->palletTypeId,
            'packingUnits' => $this->packingUnits,
            'packingUnitTypeId' => $this->packingUnitTypeId,
            'transportationCosts' => $this->transportationCosts,
            'storageCosts' => $this->storageCosts,
            'customs' => $this->customs,
            'operatingCosts' => $this->operatingCosts,
            'vatId' => $this->vatId,
            'bundleType' => $this->bundleType,
            'automaticClientVisibility' => $this->automaticClientVisibility,
            'automaticListVisibility' => $this->automaticListVisibility,
            'isHiddenInCategoryList' => $this->isHiddenInCategoryList,
            'defaultShippingCosts' => $this->defaultShippingCosts,
            'mayShowUnitPrice' => $this->mayShowUnitPrice,
            'parentVariationId' => $this->parentVariationId,
            'parentVariationQuantity' => $this->parentVariationQuantity,
            'singleItemCount' => $this->singleItemCount,
            'hasCalculatedBundleWeight' => $this->hasCalculatedBundleWeight,
            'hasCalculatedBundleNetWeight' => $this->hasCalculatedBundleNetWeight,
            'hasCalculatedBundlePurchasePrice' => $this->hasCalculatedBundlePurchasePrice,
            'hasCalculatedBundleMovingAveragePrice' => $this->hasCalculatedBundleMovingAveragePrice,
            'customsTariffNumber' => $this->customsTariffNumber,
            'categoriesInherited' => $this->categoriesInherited,
            'referrerInherited' => $this->referrerInherited,
            'clientsInherited' => $this->clientsInherited,
            'salesPricesInherited' => $this->salesPricesInherited,
            'supplierInherited' => $this->supplierInherited,
            'warehousesInherited' => $this->warehousesInherited,
            'propertiesInherited' => $this->propertiesInherited,
            'tagsInherited' => $this->tagsInherited,
            'item' => $this->item,
        ];
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function getMainVariationId(): ?int
    {
        return $this->mainVariationId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    public function getEstimatedAvailableAt(): ?DateTimeInterface
    {
        return $this->estimatedAvailableAt;
    }

    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }

    public function getMovingAveragePrice(): ?float
    {
        return $this->movingAveragePrice;
    }

    public function getPriceCalculationId(): ?string
    {
        return $this->priceCalculationId;
    }

    public function getPicking()
    {
        return $this->picking;
    }

    public function getStockLimitation(): int
    {
        return $this->stockLimitation;
    }

    public function isVisibleIfNetStockIsPositive(): bool
    {
        return $this->isVisibleIfNetStockIsPositive;
    }

    public function isInvisibleIfNetStockIsNotPositive(): bool
    {
        return $this->isInvisibleIfNetStockIsNotPositive;
    }

    public function isAvailableIfNetStockIsPositive(): bool
    {
        return $this->isAvailableIfNetStockIsPositive;
    }

    public function isUnavailableIfNetStockIsNotPositive(): bool
    {
        return $this->isUnavailableIfNetStockIsNotPositive;
    }

    public function isVisibleInListIfNetStockIsPositive(): bool
    {
        return $this->isVisibleInListIfNetStockIsPositive;
    }

    public function isInvisibleInListIfNetStockIsNotPositive(): bool
    {
        return $this->isInvisibleInListIfNetStockIsNotPositive;
    }

    public function getMainWarehouseId(): int
    {
        return $this->mainWarehouseId;
    }

    public function getMaximumOrderQuantity(): int
    {
        return $this->maximumOrderQuantity;
    }

    public function getMinimumOrderQuantity(): ?int
    {
        return $this->minimumOrderQuantity;
    }

    public function getIntervalOrderQuantity(): ?int
    {
        return $this->intervalOrderQuantity;
    }

    public function getAvailableUntil(): ?DateTimeInterface
    {
        return $this->availableUntil;
    }

    public function getReleasedAt(): ?DateTimeInterface
    {
        return $this->releasedAt;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getWeightG(): int
    {
        return $this->weightG;
    }

    public function getWeightNetG(): int
    {
        return $this->weightNetG;
    }

    public function getWidthMM(): int
    {
        return $this->widthMM;
    }

    public function getLengthMM(): int
    {
        return $this->lengthMM;
    }

    public function getHeightMM(): int
    {
        return $this->heightMM;
    }

    public function getExtraShippingCharge1(): float
    {
        return $this->extraShippingCharge1;
    }

    public function getExtraShippingCharge2(): float
    {
        return $this->extraShippingCharge2;
    }

    public function getUnitsContained(): int
    {
        return $this->unitsContained;
    }

    public function getPalletTypeId(): ?int
    {
        return $this->palletTypeId;
    }

    public function getPackingUnits(): int
    {
        return $this->packingUnits;
    }

    public function getPackingUnitTypeId(): int
    {
        return $this->packingUnitTypeId;
    }

    public function getTransportationCosts(): float
    {
        return $this->transportationCosts;
    }

    public function getStorageCosts(): float
    {
        return $this->storageCosts;
    }

    public function getCustoms(): int
    {
        return $this->customs;
    }

    public function getOperatingCosts(): int
    {
        return $this->operatingCosts;
    }

    public function getVatId(): int
    {
        return $this->vatId;
    }

    public function getBundleType(): ?string
    {
        return $this->bundleType;
    }

    public function getAutomaticClientVisibility(): int
    {
        return $this->automaticClientVisibility;
    }

    public function getAutomaticListVisibility(): int
    {
        return $this->automaticListVisibility;
    }

    public function isHiddenInCategoryList(): bool
    {
        return $this->isHiddenInCategoryList;
    }

    public function getDefaultShippingCosts(): ?float
    {
        return $this->defaultShippingCosts;
    }

    public function mayShowUnitPrice(): bool
    {
        return $this->mayShowUnitPrice;
    }

    public function getParentVariationId(): ?int
    {
        return $this->parentVariationId;
    }

    public function getParentVariationQuantity(): ?int
    {
        return $this->parentVariationQuantity;
    }

    public function getSingleItemCount(): ?int
    {
        return $this->singleItemCount;
    }

    public function hasCalculatedBundleWeight(): ?bool
    {
        return $this->hasCalculatedBundleWeight;
    }

    public function hasCalculatedBundleNetWeight(): ?bool
    {
        return $this->hasCalculatedBundleNetWeight;
    }

    public function hasCalculatedBundlePurchasePrice(): ?bool
    {
        return $this->hasCalculatedBundlePurchasePrice;
    }

    public function hasCalculatedBundleMovingAveragePrice(): ?bool
    {
        return $this->hasCalculatedBundleMovingAveragePrice;
    }

    public function getCustomsTariffNumber(): ?int
    {
        return $this->customsTariffNumber;
    }

    public function areCategoriesInherited(): bool
    {
        return $this->categoriesInherited;
    }

    public function isReferrerInherited(): bool
    {
        return $this->referrerInherited;
    }

    public function areClientsInherited(): bool
    {
        return $this->clientsInherited;
    }

    public function areSalesPricesInherited(): bool
    {
        return $this->salesPricesInherited;
    }

    public function isSupplierInherited(): bool
    {
        return $this->supplierInherited;
    }

    public function areWarehousesInherited(): bool
    {
        return $this->warehousesInherited;
    }

    public function arePropertiesInherited(): bool
    {
        return $this->propertiesInherited;
    }

    public function areTagsInherited(): bool
    {
        return $this->tagsInherited;
    }

    public function getItem(): BaseItemDetails
    {
        return $this->item;
    }

    /**
     * @return Characteristic[]
     */
    public function getCharacteristics(): array
    {
        return $this->characteristics;
    }

    /**
     * @return Image[]
     */
    public function getImages(): array
    {
        return $this->images;
    }
}
