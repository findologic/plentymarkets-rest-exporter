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
    private int $id;

    private bool $isMain;

    private ?int $mainVariationId;

    private int $itemId;

    private int $categoryVariationId;

    private int $marketVariationId;

    private int $clientVariationId;

    private int $salesPriceVariationId;

    private int $supplierVariationId;

    private int $warehouseVariationId;

    private int $position;

    private bool $isActive;

    private string $number;

    private string $model;

    private string $externalId;

    private ?int $parentVariationId;

    private ?float $parentVariationQuantity;

    private int $availability;

    private ?string $estimatedAvailableAt;

    private float $purchasePrice;

    private string $createdAt;

    private string $updatedAt;

    private ?string $relatedUpdatedAt;

    private ?int $priceCalculationId;

    private ?string $picking;

    private int $stockLimitation;

    private bool $isVisibleIfNetStockIsPositive;

    private bool $isInvisibleIfNetStockIsNotPositive;

    private bool $isAvailableIfNetStockIsPositive;

    private bool $isUnavailableIfNetStockIsNotPositive;

    private int $mainWarehouseId;

    private ?float $maximumOrderQuantity;

    private ?float $minimumOrderQuantity;

    private ?float $intervalOrderQuantity;

    private ?string $availableUntil;

    private ?string $releasedAt;

    private int $unitCombinationId;

    private string $name;

    private int $weightG;

    private int $weightNetG;

    private int $widthMM;

    private int $lengthMM;

    private int $heightMM;

    private float $extraShippingCharge1;

    private float $extraShippingCharge2;

    private int $unitsContained;

    private ?int $palletTypeId;

    private int $packingUnits;

    private int $packingUnitTypeId;

    private float $transportationCosts;

    private float $storageCosts;

    private float $customs;

    private float $operatingCosts;

    private int $vatId;

    private ?string $bundleType;

    private int $automaticClientVisibility;

    private bool $isHiddenInCategoryList;

    private ?float $defaultShippingCosts;

    private bool $mayShowUnitPrice;

    private float $movingAveragePrice;

    private int $propertyVariationId;

    private int $automaticListVisibility;

    private bool $isVisibleInListIfNetStockIsPositive;

    private bool $isInvisibleInListIfNetStockIsNotPositive;

    private int $singleItemCount;

    private string $availabilityUpdatedAt;

    private int $tagVariationId;

    private ?bool $hasCalculatedBundleWeight;

    private ?bool $hasCalculatedBundleNetWeight;

    private ?bool $hasCalculatedBundlePurchasePrice;

    private ?bool $hasCalculatedBundleMovingAveragePrice;

    private ?int $salesRank;

    /** @var VariationCategory[] */
    private array $variationCategories = [];

    /** @var VariationSalesPrice[] */
    private array $variationSalesPrices = [];

    /** @var VariationAttributeValue[] */
    private array $variationAttributeValues = [];

    /** @var VariationProperty[] */
    private array $variationProperties = [];

    private array $variationBarcodes = [];

    /** @var VariationClient[] */
    private array $variationClients = [];

    /** @var Property[] */
    private array $properties = [];

    /** @var ItemImage[] */
    private array $itemImages = [];

    /** @var VariationTag[] */
    private array $tags = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->isMain = (bool)$data['isMain'];
        $this->mainVariationId = $this->getIntProperty('mainVariationId', $data);
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
        $this->parentVariationId = $this->getIntProperty('parentVariationId', $data);
        $this->parentVariationQuantity = $this->getFloatProperty('parentVariationQuantity', $data);
        $this->availability = (int)$data['availability'];
        $this->estimatedAvailableAt = $this->getStringProperty('estimatedAvailableAt', $data);
        $this->purchasePrice = (float)$data['purchasePrice'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->relatedUpdatedAt = $this->getStringProperty('relatedUpdatedAt', $data);
        $this->priceCalculationId = $this->getIntProperty('priceCalculationId', $data);
        $this->picking = $this->getStringProperty('picking', $data);
        $this->stockLimitation = (int)$data['stockLimitation'];
        $this->isVisibleIfNetStockIsPositive = (bool)$data['isVisibleIfNetStockIsPositive'];
        $this->isInvisibleIfNetStockIsNotPositive = (bool)$data['isInvisibleIfNetStockIsNotPositive'];
        $this->isAvailableIfNetStockIsPositive = (bool)$data['isAvailableIfNetStockIsPositive'];
        $this->isUnavailableIfNetStockIsNotPositive = (bool)$data['isUnavailableIfNetStockIsNotPositive'];
        $this->mainWarehouseId = (int)$data['mainWarehouseId'];
        $this->maximumOrderQuantity = $this->getFloatProperty('maximumOrderQuantity', $data);
        $this->minimumOrderQuantity = $this->getFloatProperty('minimumOrderQuantity', $data);
        $this->intervalOrderQuantity = $this->getFloatProperty('intervalOrderQuantity', $data);
        $this->availableUntil = $this->getStringProperty('availableUntil', $data);
        $this->releasedAt = $this->getStringProperty('releasedAt', $data);
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
        $this->palletTypeId = $this->getIntProperty('palletTypeId', $data);
        $this->packingUnits = (int)$data['packingUnits'];
        $this->packingUnitTypeId = (int)$data['packingUnitTypeId'];
        $this->transportationCosts = (float)$data['transportationCosts'];
        $this->storageCosts = (float)$data['storageCosts'];
        $this->customs = (float)$data['customs'];
        $this->operatingCosts = (float)$data['operatingCosts'];
        $this->vatId = (int)$data['vatId'];
        $this->bundleType = $this->getStringProperty('bundleType', $data);
        $this->automaticClientVisibility = (int)$data['automaticClientVisibility'];
        $this->isHiddenInCategoryList = (bool)$data['isHiddenInCategoryList'];
        $this->defaultShippingCosts = $this->getFloatProperty('defaultShippingCosts', $data);
        $this->mayShowUnitPrice = (bool)$data['mayShowUnitPrice'];
        $this->movingAveragePrice = (float)$data['movingAveragePrice'];
        $this->propertyVariationId = (int)$data['propertyVariationId'];
        $this->automaticListVisibility = (int)$data['automaticListVisibility'];
        $this->isVisibleInListIfNetStockIsPositive = (bool)$data['isVisibleInListIfNetStockIsPositive'];
        $this->isInvisibleInListIfNetStockIsNotPositive = (bool)$data['isInvisibleInListIfNetStockIsNotPositive'];
        $this->singleItemCount = (int)$data['singleItemCount'];
        $this->availabilityUpdatedAt = (string)$data['availabilityUpdatedAt'];
        $this->tagVariationId = (int)$data['tagVariationId'];
        $this->hasCalculatedBundleWeight = $this->getBoolProperty('hasCalculatedBundleWeight', $data);
        $this->hasCalculatedBundleNetWeight = $this->getBoolProperty('hasCalculatedBundleNetWeight', $data);
        $this->hasCalculatedBundlePurchasePrice = $this->getBoolProperty('hasCalculatedBundlePurchasePrice', $data);
        $this->hasCalculatedBundleMovingAveragePrice = $this->getBoolProperty(
            'hasCalculatedBundleMovingAveragePrice',
            $data
        );
        $this->salesRank = $this->getIntProperty('salesRank', $data);

        // None of the following are documented, entity structure may not match data perfectly
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

        $this->variationBarcodes = $data['variationBarcodes']; // Unknown structure - received only empty arrays

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
