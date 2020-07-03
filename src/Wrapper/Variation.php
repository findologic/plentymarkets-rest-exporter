<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributeResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

class Variation
{
    /** @var Config */
    private $config;

    /** @var Registry */
    private $registry;

    /** @var ItemVariation */
    private $variationEntity;

    /** @var bool */
    private $isMain;

    /** @var int */
    private $position;

    /** @var int */
    private $vatId;

    /** @var string */
    private $number;

    /** @var string */
    private $model;

    /** @var int */
    private $id;

    /** @var int */
    private $itemId;

    /** @var string[] */
    private $barcodes = [];

    /** @var array */
    private $prices = [];

    /** @var Attribute[] */
    private $attributes = [];

    /** @var Property[] */
    private $properties = [];

    public function __construct(
        Config $config,
        Registry $registry,
        ItemVariation $variationEntity
    ) {
        $this->config = $config;
        $this->variationEntity = $variationEntity;
        $this->registry = $registry;
    }

    public function processData(): void
    {
        $this->isMain = $this->variationEntity->isMain();
        $this->position = $this->variationEntity->getPosition();
        $this->vatId = $this->variationEntity->getVatId();

        $this->processIdentifiers();
        $this->processCategories();
        $this->processPrices();
        $this->processAttributes();
    }

    private function processIdentifiers(): void
    {
        $this->number = $this->variationEntity->getNumber();
        $this->model = $this->variationEntity->getModel();
        $this->id = $this->variationEntity->getId();
        $this->itemId = $this->variationEntity->getItemId();

        foreach ($this->variationEntity->getVariationBarcodes() as $barcode) {
            $this->barcodes[] = $barcode->getCode();
        }
    }

    private function processCategories(): void
    {
        /** @var CategoryResponse $categories */
        $categories = $this->registry->get('categories');

        if (!$categories) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $variationCategories = $this->variationEntity->getVariationCategories();
        foreach ($variationCategories as $variationCategory) {
            $category = $categories->findOne(['id' => $variationCategory->getCategoryId()]);

            if (!$category) {
                continue;
            }

            foreach ($category->getDetails() as $categoryDetail) {
                if (strtoupper($categoryDetail->getLang()) !== strtoupper($this->config->getLanguage())) {
                    continue;
                }

                $this->attributes[] = new Attribute('cat', [$categoryDetail->getName()]);
                $this->attributes[] = new Attribute('cat_url', [$categoryDetail->getPreviewUrl()]);
            }
        }
    }

    private function processPrices(): void
    {
        $priceIdProperty = new Property('price_id');
        foreach ($this->variationEntity->getVariationSalesPrices() as $variationSalesPrice) {
            $this->prices[] = $variationSalesPrice->getPrice();
            if (!$priceIdProperty->getAllValues()) {
                $priceIdProperty->addValue((string)$variationSalesPrice->getSalesPriceId());
            }
        }

        $this->properties[] = $priceIdProperty;
    }

    private function processAttributes(): void
    {
        /** @var AttributeResponse $attributes */
        $attributes = $this->registry->get('attributes');

        if (!$attributes) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->variationEntity->getVariationAttributeValues() as $variationAttributeValue) {
            $attribute = $attributes->findOne(['id' => $variationAttributeValue->getAttributeId()]);
            if (!$attribute) {
                continue;
            }

            $this->attributes[] = new Attribute(
                $variationAttributeValue->getAttribute()->getBackendName(),
                [$variationAttributeValue->getAttributeValue()->getBackendName()]
            );
        }
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getVatId(): int
    {
        return $this->vatId;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @return string[]
     */
    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    /**
     * @return float[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
