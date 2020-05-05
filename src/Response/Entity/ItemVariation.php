<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationCategory;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationClient;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationSalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationAttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationTag;

class ItemVariation extends Entity
{
    /** @var int */
    private $id;

    /** @var bool */
    private $isMain;

    /** @var int|null */
    private $mainVariationId;

    /** @var int */
    private $itemId;

    /** @var int */
    private $categoryVariationId;

    /** @var int */
    private $marketVariationId;

    /** @var int */
    private $clientVariationId;

    /** @var int */
    private $salesPriceVariationId;

    /** @var int */
    private $supplierVariationId;

    /** @var int */
    private $warehouseVariationId;

    /** @var int */
    private $position;

    /** @var bool */
    private $isActive;

    /** @var string */
    private $number;

    /** @var string */
    private $model;

    /** @var string */
    private $externalId;

    /** @var int|null */
    private $parentVariationId;

    /** @var float|null */
    private $parentVariationQuantity;

    /** @var int */
    private $availability;

    /** @var string|null */
    private $estimatedAvailableAt;

    /** @var float */
    private $purchasePrice;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /** @var string|null */
    private $relatedUpdatedAt;

    /** @var int|null */
    private $priceCalculationId;

    /** @var string|null */
    private $picking;

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

    /** @var int */
    private $mainWarehouseId;

    /** @var float|null */
    private $maximumOrderQuantity;

    /** @var float|null */
    private $minimumOrderQuantity;

    /** @var float|null */
    private $intervalOrderQuantity;

    /** @var string|null */
    private $availableUntil;

    /** @var string|null */
    private $releasedAt;

    /** @var int */
    private $unitCombinationId;

    /** @var string */
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

    /** @var float */
    private $customs;

    /** @var float */
    private $operatingCosts;

    /** @var int */
    private $vatId;

    /** @var string|null */
    private $bundleType;

    /** @var int */
    private $automaticClientVisibility;

    /** @var bool */
    private $isHiddenInCategoryList;

    /** @var float|null */
    private $defaultShippingCosts;

    /** @var bool */
    private $mayShowUnitPrice;

    /** @var bool */
    private $movingAveragePrice;

    /** @var int */
    private $propertyVariationId;

    /** @var int */
    private $automaticListVisibility;

    /** @var bool */
    private $isVisibleInListIfNetStockIsPositive;

    /** @var bool */
    private $isInvisibleInListIfNetStockIsNotPositive;

    /** @var int */
    private $singleItemCount;

    /** @var string */
    private $availabilityUpdatedAt;

    /** @var int */
    private $tagVariationId;

    /** @var bool|null */
    private $hasCalculatedBundleWeight;

    /** @var bool|null */
    private $hasCalculatedBundleNetWeight;

    /** @var bool|null */
    private $hasCalculatedBundlePurchasePrice;

    /** @var bool|null */
    private $hasCalculatedBundleMovingAveragePrice;

    /** @var int|null */
    private $salesRank;

    /** @var VariationCategory[] */
    private $variationCategories = [];

    /** @var VariationSalesPrice[] */
    private $variationSalesPrices = [];

    /** @var VariationAttributeValue[] */
    private $variationAttributeValues = [];

    /** @var VariationProperty[] */
    private $variationProperties = [];

    /** @var array */
    private $variationBarcodes = [];

    /** @var VariationClient[] */
    private $variationClients = [];

    /** @var Property[] */
    private $properties = [];

    /** @var ItemImage[] */
    private $itemImages = [];

    /** @var VariationTag[] */
    private $tags = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->isMain = (bool)$data['isMain'];
        $this->mainVariationId = is_null($data['mainVariationId']) ? null : (int)$data['mainVariationId'];
        $this->itemId = (int)$data['itemId'];
        $this->categoryVariationId = (int)$data['categoryVariationId'];
        $this->marketVariationId = (int)$data['marketVariationId'];
        $this->clientVariationId = (int)$data['clientVariationId'];
        $this->salesPriceVariationId = (int)$data['salesPriceVariationId'];
        $this->supplierVariationId = (int)$data['supplierVariationId'];
        $this->warehouseVariationId = (int)$data['warehouseVariationId'];
        $this->position = (int)$data['position'];
        $this->isActive = (bool)$data['isActive'];
        $this->number = (string)$data['number'];
        $this->model = (string)$data['model'];
        $this->externalId = (string)$data['externalId'];
        $this->parentVariationId = is_null($data['parentVariationId']) ? null : (int)$data['parentVariationId'];
        $this->parentVariationQuantity = null;
        if (!is_null($data['parentVariationQuantity'])) {
            $this->parentVariationQuantity = (float)$data['parentVariationQuantity'];
        }
        $this->availability = (int)$data['availability'];
        $this->estimatedAvailableAt = null;
        if (!is_null($data['estimatedAvailableAt'])) {
            $this->estimatedAvailableAt = (string)$data['estimatedAvailableAt'];
        }
        $this->purchasePrice = (float)$data['purchasePrice'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->relatedUpdatedAt = is_null($data['relatedUpdatedAt']) ? null : (string)$data['relatedUpdatedAt'];
        $this->priceCalculationId = is_null($data['priceCalculationId']) ? null : (int)$data['priceCalculationId'];
        $this->picking = is_null($data['picking']) ? null : (string)$data['picking'];
        $this->stockLimitation = (int)$data['stockLimitation'];
        $this->isVisibleIfNetStockIsPositive = (bool)$data['isVisibleIfNetStockIsPositive'];
        $this->isInvisibleIfNetStockIsNotPositive = (bool)$data['isInvisibleIfNetStockIsNotPositive'];
        $this->isAvailableIfNetStockIsPositive = (bool)$data['isAvailableIfNetStockIsPositive'];
        $this->isUnavailableIfNetStockIsNotPositive = (bool)$data['isUnavailableIfNetStockIsNotPositive'];
        $this->mainWarehouseId = (int)$data['mainWarehouseId'];
        $this->maximumOrderQuantity = null;
        if (!is_null($data['maximumOrderQuantity'])) {
            $this->maximumOrderQuantity = (float)$data['maximumOrderQuantity'];
        }
        $this->minimumOrderQuantity = null;
        if (!is_null($data['minimumOrderQuantity'])) {
            $this->minimumOrderQuantity = (float)$data['minimumOrderQuantity'];
        }
        $this->intervalOrderQuantity = null;
        if (!is_null($data['intervalOrderQuantity'])) {
            $this->intervalOrderQuantity = (float)$data['intervalOrderQuantity'];
        }
        $this->availableUntil = is_null($data['availableUntil']) ? null : (string)$data['availableUntil'];
        $this->releasedAt = is_null($data['releasedAt']) ? null : (string)$data['releasedAt'];
        $this->unitCombinationId = (int)$data['unitCombinationId'];
        $this->name = (string)$data['name'];
        $this->weightG = (int)$data['weightG'];
        $this->weightNetG = (int)$data['weightNetG'];
        $this->widthMM = (int)$data['widthMM'];
        $this->lengthMM = (int)$data['lengthMM'];
        $this->heightMM = (int)$data['heightMM'];
        $this->extraShippingCharge1 = (float)$data['extraShippingCharge1'];
        $this->extraShippingCharge2 = (float)$data['extraShippingCharge2'];
        $this->unitsContained = (int)$data['unitsContained'];
        $this->palletTypeId = is_null($data['palletTypeId']) ? null : (int)$data['palletTypeId'];
        $this->packingUnits = (int)$data['packingUnits'];
        $this->packingUnitTypeId = (int)$data['packingUnitTypeId'];
        $this->transportationCosts = (float)$data['transportationCosts'];
        $this->storageCosts = (float)$data['storageCosts'];
        $this->customs = (float)$data['customs'];
        $this->operatingCosts = (float)$data['operatingCosts'];
        $this->vatId = (int)$data['vatId'];
        $this->bundleType = is_null($data['bundleType']) ? null : (string)$data['bundleType'];
        $this->automaticClientVisibility = (int)$data['automaticClientVisibility'];
        $this->isHiddenInCategoryList = (bool)$data['isHiddenInCategoryList'];
        $this->defaultShippingCosts = null;
        if (!is_null($data['defaultShippingCosts'])) {
            $this->defaultShippingCosts = (float)$data['defaultShippingCosts'];
        }
        $this->mayShowUnitPrice = (bool)$data['mayShowUnitPrice'];
        $this->movingAveragePrice = (float)$data['movingAveragePrice'];
        $this->propertyVariationId = (int)$data['propertyVariationId'];
        $this->automaticListVisibility = (int)$data['automaticListVisibility'];
        $this->isVisibleInListIfNetStockIsPositive = (bool)$data['isVisibleInListIfNetStockIsPositive'];
        $this->isInvisibleInListIfNetStockIsNotPositive = (bool)$data['isInvisibleInListIfNetStockIsNotPositive'];
        $this->singleItemCount = (int)$data['singleItemCount'];
        $this->availabilityUpdatedAt = (string)$data['availabilityUpdatedAt'];
        $this->tagVariationId = (int)$data['tagVariationId'];
        $this->hasCalculatedBundleWeight = null;
        if (!is_null($data['hasCalculatedBundleWeight'])) {
            $this->hasCalculatedBundleWeight = (bool)$data['hasCalculatedBundleWeight'];
        }
        $this->hasCalculatedBundleNetWeight = null;
        if (!is_null($data['hasCalculatedBundleNetWeight'])) {
            $this->hasCalculatedBundleNetWeight = (bool)$data['hasCalculatedBundleNetWeight'];
        }
        $this->hasCalculatedBundlePurchasePrice = null;
        if (!is_null($data['hasCalculatedBundlePurchasePrice'])) {
            $this->hasCalculatedBundlePurchasePrice = (bool)$data['hasCalculatedBundlePurchasePrice'];
        }
        $this->hasCalculatedBundleMovingAveragePrice = null;
        if (!is_null($data['hasCalculatedBundleMovingAveragePrice'])) {
            $this->hasCalculatedBundleMovingAveragePrice = (bool)$data['hasCalculatedBundleMovingAveragePrice'];
        }
        $this->salesRank = is_null($data['salesRank']) ? null : (int)$data['salesRank'];

        //Note - none of the following are documented, entity structure may not match data perfectly
        if (!empty($data['variationCategories'])) {
            foreach ($data['variationCategories'] as $variationCategory) {
                $this->variationCategories[] = new VariationCategory($variationCategory);
            }
        }

        if (!empty($data['variationSalesPrices'])) {
            foreach ($data['variationSalesPrices'] as $variationSalesPrice) {
                $this->variationSalesPrices[] = new VariationSalesPrice($variationSalesPrice);
            }
        }

        if (!empty($data['variationAttributeValues'])) {
            foreach ($data['variationAttributeValues'] as $variationAttributeValue) {
                $this->variationAttributeValues[] = new VariationAttributeValue($variationAttributeValue);
            }
        }

        if (!empty($data['variationProperties'])) {
            foreach ($data['variationProperties'] as $variationProperty) {
                $this->variationProperties[] = new VariationProperty($variationProperty);
            }
        }

        $this->variationBarcodes = $data['variationBarcodes']; //Unknown structure - received only empty arrays

        if (!empty($data['variationClients'])) {
            foreach ($data['variationClients'] as $variationClient) {
                $this->variationClients[] = new VariationClient($variationClient);
            }
        }

        if (!empty($data['properties'])) {
            foreach ($data['properties'] as $property) {
                $this->properties[] = new Property($property);
            }
        }

        if (!empty($data['itemImages'])) {
            foreach ($data['itemImages'] as $itemImage) {
                $this->itemImages[] = new ItemImage($itemImage);
            }
        }

        if (!empty($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $this->tags[] = new VariationTag($tag);
            }
        }
    }

    public function getData(): array
    {

        $variationCategories = [];
        foreach ($this->variationCategories as $variationCategory) {
            $variationCategories[] = $variationCategory->getData();
        }

        $variationSalesPrices = [];
        foreach ($this->variationSalesPrices as $variationSalesPrice) {
            $variationSalesPrices[] = $variationSalesPrice->getData();
        }

        $variationAttributeValues = [];
        foreach ($this->variationAttributeValues as $variationAttributeValue) {
            $variationAttributeValues[] = $variationAttributeValue->getData();
        }

        $variationProperties = [];
        foreach ($this->variationProperties as $variationProperty) {
            $variationProperties[] = $variationProperty->getData();
        }

        $variationClients = [];
        foreach ($this->variationClients as $variationClient) {
            $variationClients[] = $variationClient->getData();
        }

        $properties = [];
        foreach ($this->properties as $property) {
            $properties[] = $property->getData();
        }

        $itemImages = [];
        foreach ($this->itemImages as $itemImage) {
            $itemImages[] = $itemImage->getData();
        }

        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->getData();
        }

        return [
            'id' => $this->id,
            'isMain' => $this->isMain,
            'mainVariationId' => $this->mainVariationId,
            'itemId' => $this->itemId,
            'categoryVariationId' => $this->categoryVariationId,
            'marketVariationId' => $this->marketVariationId,
            'clientVariationId' => $this->clientVariationId,
            'salesPriceVariationId' => $this->salesPriceVariationId,
            'supplierVariationId' => $this->supplierVariationId,
            'warehouseVariationId' => $this->warehouseVariationId,
            'position' => $this->position,
            'isActive' => $this->isActive,
            'number' => $this->number,
            'model' => $this->model,
            'externalId' => $this->externalId,
            'parentVariationId' => $this->parentVariationId,
            'parentVariationQuantity' => $this->parentVariationQuantity,
            'availability' => $this->availability,
            'estimatedAvailableAt' => $this->estimatedAvailableAt,
            'purchasePrice' => $this->purchasePrice,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'relatedUpdatedAt' => $this->relatedUpdatedAt,
            'priceCalculationId' => $this->priceCalculationId,
            'picking' => $this->picking,
            'stockLimitation' => $this->stockLimitation,
            'isVisibleIfNetStockIsPositive' => $this->isVisibleIfNetStockIsPositive,
            'isInvisibleIfNetStockIsNotPositive' => $this->isInvisibleIfNetStockIsNotPositive,
            'isAvailableIfNetStockIsPositive' => $this->isAvailableIfNetStockIsPositive,
            'isUnavailableIfNetStockIsNotPositive' => $this->isUnavailableIfNetStockIsNotPositive,
            'mainWarehouseId' => $this->mainWarehouseId,
            'maximumOrderQuantity' => $this->maximumOrderQuantity,
            'minimumOrderQuantity' => $this->minimumOrderQuantity,
            'intervalOrderQuantity' => $this->intervalOrderQuantity,
            'availableUntil' => $this->availableUntil,
            'releasedAt' => $this->releasedAt,
            'unitCombinationId' => $this->unitCombinationId,
            'name' => $this->name,
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
            'isHiddenInCategoryList' => $this->isHiddenInCategoryList,
            'defaultShippingCosts' => $this->defaultShippingCosts,
            'mayShowUnitPrice' => $this->mayShowUnitPrice,
            'movingAveragePrice' => $this->movingAveragePrice,
            'propertyVariationId' => $this->propertyVariationId,
            'automaticListVisibility' => $this->automaticListVisibility,
            'isVisibleInListIfNetStockIsPositive' => $this->isVisibleInListIfNetStockIsPositive,
            'isInvisibleInListIfNetStockIsNotPositive' => $this->isInvisibleInListIfNetStockIsNotPositive,
            'singleItemCount' => $this->singleItemCount,
            'availabilityUpdatedAt' => $this->availabilityUpdatedAt,
            'tagVariationId' => $this->tagVariationId,
            'hasCalculatedBundleWeight' => $this->hasCalculatedBundleWeight,
            'hasCalculatedBundleNetWeight' => $this->hasCalculatedBundleNetWeight,
            'hasCalculatedBundlePurchasePrice' => $this->hasCalculatedBundlePurchasePrice,
            'hasCalculatedBundleMovingAveragePrice' => $this->hasCalculatedBundleMovingAveragePrice,
            'salesRank' => $this->salesRank,
            'variationCategories' => $variationCategories,
            'variationSalesPrices' => $variationSalesPrices,
            'variationAttributeValues' => $variationAttributeValues,
            'variationProperties' => $variationProperties,
            'variationBarcodes' => $this->variationBarcodes,
            'variationClients' => $variationClients,
            'properties' => $properties,
            'itemImages' => $itemImages,
            'tags' => $tags
        ];
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getCategoryVariationId(): int
    {
        return $this->categoryVariationId;
    }

    public function getMarketVariationId(): int
    {
        return $this->marketVariationId;
    }

    public function getClientVariationId(): int
    {
        return $this->clientVariationId;
    }

    public function getSalesPriceVariationId(): int
    {
        return $this->salesPriceVariationId;
    }

    public function getSupplierVariationId(): int
    {
        return $this->supplierVariationId;
    }

    public function getWarehouseVariationId(): int
    {
        return $this->warehouseVariationId;
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

    public function getModel(): string
    {
        return $this->model;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getParentVariationId(): ?int
    {
        return $this->parentVariationId;
    }

    public function getParentVariationQuantity(): ?float
    {
        return $this->parentVariationQuantity;
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    public function getEstimatedAvailableAt(): ?string
    {
        return $this->estimatedAvailableAt;
    }

    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getRelatedUpdatedAt(): ?string
    {
        return $this->relatedUpdatedAt;
    }

    public function getPriceCalculationId(): int
    {
        return $this->priceCalculationId;
    }

    public function getPicking(): ?string
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

    public function getMainWarehouseId(): int
    {
        return $this->mainWarehouseId;
    }

    public function getMaximumOrderQuantity(): ?float
    {
        return $this->maximumOrderQuantity;
    }

    public function getMinimumOrderQuantity(): ?float
    {
        return $this->minimumOrderQuantity;
    }

    public function getIntervalOrderQuantity(): ?float
    {
        return $this->intervalOrderQuantity;
    }

    public function getAvailableUntil(): ?string
    {
        return $this->availableUntil;
    }

    public function getReleasedAt(): ?string
    {
        return $this->releasedAt;
    }

    public function getUnitCombinationId(): int
    {
        return $this->unitCombinationId;
    }

    public function getName(): string
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

    public function getCustoms(): float
    {
        return $this->customs;
    }

    public function getOperatingCosts(): float
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

    public function isHiddenInCategoryList(): bool
    {
        return $this->isHiddenInCategoryList;
    }

    public function getDefaultShippingCosts(): ?float
    {
        return $this->defaultShippingCosts;
    }

    public function getMayShowUnitPrice(): bool
    {
        return $this->mayShowUnitPrice;
    }

    public function getMovingAveragePrice(): float
    {
        return $this->movingAveragePrice;
    }

    public function getPropertyVariationId(): int
    {
        return $this->propertyVariationId;
    }

    public function getAutomaticListVisibility(): int
    {
        return $this->automaticListVisibility;
    }

    public function isVisibleInListIfNetStockIsPositive(): bool
    {
        return $this->isVisibleInListIfNetStockIsPositive;
    }

    public function isInvisibleInListIfNetStockIsNotPositive(): bool
    {
        return $this->isInvisibleInListIfNetStockIsNotPositive;
    }

    public function getSingleItemCount(): int
    {
        return $this->singleItemCount;
    }

    public function getAvailabilityUpdatedAt(): string
    {
        return $this->availabilityUpdatedAt;
    }

    public function getTagVariationId(): int
    {
        return $this->tagVariationId;
    }

    public function getHasCalculatedBundleWeight(): ?bool
    {
        return $this->hasCalculatedBundleWeight;
    }

    public function getHasCalculatedBundleNetWeight(): ?bool
    {
        return $this->hasCalculatedBundleNetWeight;
    }

    public function getHasCalculatedBundlePurchasePrice(): ?bool
    {
        return $this->hasCalculatedBundlePurchasePrice;
    }

    public function getHasCalculatedBundleMovingAveragePrice(): ?bool
    {
        return $this->hasCalculatedBundleMovingAveragePrice;
    }

    public function getSalesRank(): ?int
    {
        return $this->salesRank;
    }

    /**
     * @return VariationCategory[]
     */
    public function getVariationCategories(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->variationCategories;
    }

    /**
     * @return VariationSalesPrice[]
     */
    public function getVariationSalesPrices(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->variationSalesPrices;
    }

    /**
     * @return VariationAttributeValue[]
     */
    public function getVariationAttributeValues(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->variationAttributeValues;
    }

    /**
     * @return VariationProperty[]
     */
    public function getVariationProperties(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->variationProperties;
    }

    public function getVariationBarcodes(): array
    {
        return $this->variationBarcodes;
    }

    /**
     * @return VariationClient[]
     */
    public function getVariationClients(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->variationClients;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->properties;
    }

    /**
     * @return ItemImage[]
     */
    public function getItemImages(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->itemImages;
    }

    /**
     * @return VariationTag[]
     */
    public function getTags(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->tags;
    }
}
