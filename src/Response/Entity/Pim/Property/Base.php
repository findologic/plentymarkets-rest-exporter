<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Base extends Entity
{
    private ?bool $isMain;
    private ?int $mainVariationId;
    private int $itemId;
    private ?int $position;
    private ?bool $isActive;
    private ?string $number;
    private ?string $model;
    private ?string $externalId;
    private ?int $availability;
    private ?DateTimeInterface $estimatedAvailableAt;
    private ?float $purchasePrice;
    private ?float $movingAveragePrice;
    private ?string $priceCalculationId;
    /** @var null Unknown data. */
    private $picking = null;
    private ?int $stockLimitation;
    private ?bool $isVisibleIfNetStockIsPositive;
    private ?bool $isInvisibleIfNetStockIsNotPositive;
    private ?bool $isAvailableIfNetStockIsPositive;
    private ?bool $isUnavailableIfNetStockIsNotPositive;
    private ?bool $isVisibleInListIfNetStockIsPositive;
    private ?bool $isInvisibleInListIfNetStockIsNotPositive;
    private ?int $mainWarehouseId;
    private ?int $maximumOrderQuantity;
    private ?int $minimumOrderQuantity;
    private ?int $intervalOrderQuantity;
    private ?DateTimeInterface $availableUntil;
    private ?DateTimeInterface $releasedAt;
    private ?string $name;
    private ?int $weightG;
    private ?int $weightNetG;
    private ?int $widthMM;
    private ?int $lengthMM;
    private ?int $heightMM;
    private ?float $extraShippingCharge1;
    private ?float $extraShippingCharge2;
    private ?int $unitsContained;
    private ?int $palletTypeId;
    private ?int $packingUnits;
    private ?int $packingUnitTypeId;
    private ?float $transportationCosts;
    private ?float $storageCosts;
    private ?int $customs;
    private ?int $operatingCosts;
    private ?int $vatId;
    private ?string $bundleType;
    private ?int $automaticClientVisibility;
    private ?int $automaticListVisibility;
    private ?bool $isHiddenInCategoryList;
    private ?float $defaultShippingCosts;
    private ?bool $mayShowUnitPrice;
    private ?int $parentVariationId;
    private ?int $parentVariationQuantity;
    private ?int $singleItemCount;
    private ?bool $hasCalculatedBundleWeight;
    private ?bool $hasCalculatedBundleNetWeight;
    private ?bool $hasCalculatedBundlePurchasePrice;
    private ?bool $hasCalculatedBundleMovingAveragePrice;
    private ?int $customsTariffNumber;
    private ?bool $categoriesInherited;
    private ?bool $referrerInherited;
    private ?bool $clientsInherited;
    private ?bool $salesPricesInherited;
    private ?bool $supplierInherited;
    private ?bool $warehousesInherited;
    private ?bool $propertiesInherited;
    private ?bool $tagsInherited;
    private ?BaseItemDetails $item;
    /** @var Characteristic[] */
    private array $characteristics;
    /** @var Image[] */
    private array $images;

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

    public function getPosition(): ?int
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

    public function getVatId(): ?int
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
