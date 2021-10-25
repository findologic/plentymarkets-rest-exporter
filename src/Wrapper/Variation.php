<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\InsteadPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Property;

class Variation
{
    private Config $config;
    private RegistryService $registryService;
    private Item $item;
    /** @var PimVariation[] */
    private array $variationEntities;

    /**
     * @param PimVariation[] $variationEntities
     */
    public function __construct(
        Config $config,
        RegistryService $registryService,
        Item $item,
        array $variationEntities
    ) {
        $this->config = $config;
        $this->registryService = $registryService;
        $this->item = $item;
        $this->variationEntities = $variationEntities;
    }

    public function process(bool $checkAvailability = true): int
    {
        $hasImage = false;
        $hasCategories = false;
        $variationsProcessed = 0;
        /** @var Price[] $prices */
        $prices = [];
        /** @var InsteadPrice[] $insteadPrices */
        $insteadPrices = [];
        /** @var Ordernumber[] $ordernumbers */
        $ordernumbers = [];
        $highestPosition = 0;
        /** @var Property|null $baseUnit */
        $baseUnit = null;
        /** @var Property|null $packageSize */
        $packageSize = null;
        /** @var Property|null $variationIdProperty */
        $variationIdProperty = null;

        foreach ($this->variationEntities as $variationEntity) {
            if (!$this->shouldExportVariation($variationEntity, $checkAvailability)) {
                continue;
            }

            $categories = $this->categoryAttributeAdapter->adaptVariation($variationEntity);
            foreach ($categories as $category) {
                $hasCategories = true;
                $this->item->addMergedAttribute($category);
            }

            $variationAttributes = $this->variationAttributesAttributeAdapter->adaptVariation($variationEntity);
            foreach ($variationAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }

            $tagData = $this->tagAdapter->adaptVariation($variationEntity);
            foreach ($tagData as $serializedField) {
                switch (true) {
                    case $serializedField instanceof Attribute:
                        $this->item->addMergedAttribute($serializedField);
                        break;
                    case $serializedField instanceof Keyword:
                        $this->item->addKeyword($serializedField);
                        break;
                    default:
                        break;
                }
            }

            $variation = new Variation($this->config, $this->registryService, $variationEntity);
            $variation->process();

            if (!$hasImage && $image = $this->imageAdapter->adaptVariation($variationEntity)) {
                $this->item->addImage($image);
                $hasImage = true;
            }

            foreach ($this->clientGroupAdapter->adaptVariation($variationEntity) as $group) {
                $this->item->addUsergroup($group);
            }

//            foreach ($variation->getTags() as $tag) {
//                $this->item->addKeyword($tag);
//            }

            if (!$packageSize) {
                $packageSize = $this->packageSizePropertyAdapter->adaptVariation($variationEntity);
            }

            if (!$baseUnit) {
                $baseUnit = $this->unitPropertyAdapter->adaptVariation($variationEntity);
            }

            if (!$variationIdProperty || $variationEntity->getBase()->isMain()) {
                $variationIdProperty = $this->variationIdPropertyAdapter->adaptVariation($variationEntity);
            }

            $position = $variationEntity->getBase()->getPosition();
            if ($variationEntity->getBase()->isMain() || !$this->item->getSort()->getValues()) {
                // Only add sort in case the variation has a position.
                if ($variationEntity->getBase()->getPosition()) {
                    $this->item->addSort($variationEntity->getBase()->getPosition());
                }
            }
            $highestPosition = $position > $highestPosition ? $position : $highestPosition;

            $ordernumbers = array_merge(
                $ordernumbers,
                $this->identifierOrdernumberAdapter->adaptVariation($variationEntity)
            );

            $characteristicAttributes = $this->characteristicsAttributeAdapter->adaptVariation($variationEntity);
            foreach ($characteristicAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }
            $propertyAttributes = $this->propertyAttributeAdapter->adaptVariation($variationEntity);
            foreach ($propertyAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }
//            foreach ($variation->getAttributes() as $attribute) {
//                $this->item->addMergedAttribute($attribute);
//            }

            $allPrices = $this->priceAdapter->adaptVariation($variationEntity);
            foreach ($allPrices as $exportPrice) {
                if ($exportPrice instanceof Price) {
                    $prices[] = $exportPrice;
                }
                if ($exportPrice instanceof InsteadPrice) {
                    $insteadPrices[] = $exportPrice;
                }
            }

//            if ($variation->hasCategories()) {
//                $hasCategories = true;
//            }

            $variationsProcessed++;
        }

        // If no children have categories, we're skipping this product.
        if (!$hasCategories) {
            return 0;
        }

        // VatRate should be set from the last variation, therefore this code outside the foreach loop
        if (isset($variationEntity)) {
            $taxRate = $this->vatRateTaxRateAdapter->adaptVariation($variationEntity);
            if ($taxRate) {
                $this->item->setTaxRate($taxRate->getSimpleValue());
            }
        }

        if ($prices) {
            $this->item->addPrice(Utils::getLowestValue($prices));
        }

        if ($insteadPrices) {
            $this->item->setInsteadPrice(Utils::getLowestValue($insteadPrices));
        }

        $ordernumbers = array_unique($ordernumbers, SORT_REGULAR);
        $this->item->setAllOrdernumbers($ordernumbers);

        $salesFrequency = $this->storeConfiguration->getItemSortByMonthlySales() ? $highestPosition : 0;
        $this->item->addSalesFrequency($salesFrequency);

        if ($baseUnit) {
            $this->item->addProperty($baseUnit);
        }

        if ($packageSize) {
            $this->item->addProperty($packageSize);
        }

        if ($variationIdProperty) {
            $this->item->addProperty($variationIdProperty);
        }

        return $variationsProcessed;
    }

    private function shouldExportVariation(PimVariation $variation, bool $checkAvailability = true): bool
    {
        if (!$variation->getBase()->isActive()) {
            return false;
        }

        if ($variation->getBase()->getAutomaticListVisibility() < 1) {
            return false;
        }

        /** @var CarbonInterface|null $availableUntil */
        $availableUntil = $variation->getBase()->getAvailableUntil();
        if ($availableUntil !== null && $availableUntil->lessThan(Carbon::now())) {
            return false;
        }

        if ($checkAvailability && $this->config->getAvailabilityId() !== null) {
            if ($variation->getBase()->getAvailability() === $this->config->getAvailabilityId()) {
                return false;
            }
        }

        if ($variation->hasExportExclusionTag($this->config->getLanguage())) {
            return false;
        }

        return true;
    }
}
