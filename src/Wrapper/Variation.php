<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Data\Usergroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributeResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertyGroupResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertyResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\SalesPriceResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\WebStoreResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property as ItemVariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup;

class Variation
{
    public const AVAILABILITY_STORE = 'mandant';

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

    /** @var float */
    private $price;

    /** @var float */
    private $insteadPrice;

    /** @var Attribute[] */
    private $attributes = [];

    /** @var Property[] */
    private $properties = [];

    /** @var Usergroup[] */
    private $groups;

    /** @var Keyword[] */
    private $tags = [];

    /** @var int|null */
    private $salesRank;

    /** @var Image */
    private $image;

    /** @var PropertyGroupResponse */
    private $propertyGroups;

    /** @var PropertySelectionResponse */
    private $propertySelections;

    public function __construct(
        Config $config,
        Registry $registry,
        ItemVariation $variationEntity
    ) {
        $this->config = $config;
        $this->variationEntity = $variationEntity;
        $this->registry = $registry;
        $this->propertyGroups = $this->registry->get('propertyGroups');
        $this->propertySelections = $this->registry->get('propertySelections');
    }

    public function processData(): void
    {
        $this->isMain = $this->variationEntity->isMain();
        $this->position = $this->variationEntity->getPosition();
        $this->vatId = $this->variationEntity->getVatId();
        $this->salesRank = $this->variationEntity->getSalesRank();

        $this->processIdentifiers();
        $this->processCategories();
        $this->processPrices();
        $this->processAttributes();
        $this->processGroups();
        $this->processTags();
        $this->processImages();
        $this->processVariationProperties();
        $this->processVariationSpecificProperties();
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getInsteadPrice(): float
    {
        return (float)$this->insteadPrice;
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

    /**
     * @return Usergroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return Keyword[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getSalesRank(): ?int
    {
        // @codeCoverageIgnoreStart
        // TODO: test when becomes relevant
        return $this->salesRank;
        // @codeCoverageIgnoreEnd
    }

    public function getImage(): Image
    {
        return $this->image;
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
        /** @var SalesPriceResponse $salesPrices */
        $salesPrices = $this->registry->get('salesPrices');

        $priceId = $this->config->getPriceId() ?: $salesPrices->findOne(['type' => 'default'])->getId();
        $insteadPriceId = $this->config->getRrpId() ?: $salesPrices->findOne(['type' => 'rrp'])->getId();

        $priceIdProperty = new Property('price_id');
        $priceIdProperty->addValue((string)$priceId);
        $this->properties[] = $priceIdProperty;

        foreach ($this->variationEntity->getVariationSalesPrices() as $variationSalesPrice) {
            $price = $variationSalesPrice->getPrice();
            if ($price == 0) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            if ($variationSalesPrice->getSalesPriceId() == $priceId) {
                if (!$this->price) {
                    $this->price = $price;
                // @codeCoverageIgnoreStart
                // not sure if this can ever actually happen
                } elseif ($this->price > $price) {
                    $this->price = $price;
                }
                // @codeCoverageIgnoreEnd
            }
            if ($variationSalesPrice->getSalesPriceId() == $insteadPriceId) {
                $this->insteadPrice = $price;
            }
        }
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

    private function processGroups(): void
    {
        /** @var WebStoreResponse $stores */
        $stores = $this->registry->get('stores');
        $variationClients = $this->variationEntity->getVariationClients();
        foreach ($variationClients as $variationClient) {
            if ($store = $stores->getWebStoreByStoreIdentifier($variationClient->getPlentyId())) {
                $this->groups[] = new Usergroup($store->getId() . '_');
            }
        }
    }

    private function processTags(): void
    {
        $tags = $this->variationEntity->getTags();

        $tagIds = [];
        foreach ($tags as $tag) {
            if ($tag->getTagType() !== 'variation') {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $tagIds[] = $tag->getTagId();

            $correctTagName = $tag->getTag()->getTagName();

            foreach ($tag->getTag()->getNames() as $tagName) {
                if ($tagName->getTagLang() === strtolower($this->config->getLanguage())) {
                    $correctTagName = $tagName->getTagName();

                    break;
                }
            }

            $this->tags[] = new Keyword($correctTagName);
        }

        if ($tagIds) {
            $this->attributes[] = new Attribute('cat_id', $tagIds);
        }
    }

    private function processImages(): void
    {
        $images = $this->variationEntity->getItemImages();

        foreach ($images as $image) {
            $imageAvailabilities = $image->getAvailabilities();
            foreach ($imageAvailabilities as $imageAvailability) {
                if ($imageAvailability->getType() == self::AVAILABILITY_STORE) {
                    $this->image = new Image($image->getUrlMiddle());

                    return;
                }
            }
        }
    }

    private function processVariationProperties(): void
    {
        $variationProperties = $this->variationEntity->getVariationProperties();

        foreach ($variationProperties as $variationProperty) {
            $itemProperty = $variationProperty->getProperty();

            if ($itemProperty && !$itemProperty->isSearchable()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            if ($itemProperty->getValueType() === 'empty' && !$itemProperty->getPropertyGroupId()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $value = $this->getPropertyValue($variationProperty);
            // If there is no value for the property, use its name as value and the group name as the property name.
            // Properties of type "empty" are a special case since they never have a value of their own.
            if ($itemProperty->getValueType() === 'empty') {
                $propertyName = $this->getPropertyGroupForPropertyName($itemProperty->getPropertyGroupId());
            } elseif ($value === '') {
                $propertyName = $this->getPropertyGroupForPropertyName($itemProperty->getPropertyGroupId());
                $value = $variationProperty->getPropertyName($this->config->getLanguage());
            } else {
                $propertyName = $variationProperty->getPropertyName($this->config->getLanguage());
            }

            if ($propertyName != null && $value != "null" && $value != null && $value != '') {
                $this->attributes[] = new Attribute($propertyName, [$value]);
            }
        }
    }

    private function processVariationSpecificProperties(): void
    {
        /** @var PropertyResponse $properties */
        if (!$properties = $this->registry->get('properties')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->variationEntity->getProperties() as $property) {
            if ($property->getRelationTypeIdentifier() != ItemVariationProperty::PROPERTY_TYPE_ITEM) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $propertyName = $properties->getPropertyName($property->getPropertyId(), $this->config->getLanguage());

            $value = null;

            $cast = $property->getPropertyRelation()->getCast();
            switch ($cast) {
                case 'empty':
                    // @codeCoverageIgnoreStart
                    break;
                    // @codeCoverageIgnoreEnd
                case 'shortText':
                case 'longText':
                    foreach ($property->getRelationValues() as $relationValue) {
                        if (strtoupper($relationValue->getLang()) != strtoupper($this->config->getLanguage())) {
                            continue;
                        }

                        $value = $relationValue->getValue();
                    }
                    break;
                case 'selection':
                    if (!$relationValues = $property->getRelationValues()) {
                        // @codeCoverageIgnoreStart
                        break;
                        // @codeCoverageIgnoreEnd
                    }
                    $selectionId = (int)reset($relationValues)->getValue();
                    $value = $properties->getPropertySelectionValue(
                        $property->getPropertyId(),
                        $selectionId,
                        $this->config->getLanguage()
                    );
                    break;
                case 'multiSelection':
                    $value = $this->propertySelections->getPropertySelectionValues(
                        $property->getPropertyId(),
                        $this->config->getLanguage()
                    );
                    break;
                default:
                    // @codeCoverageIgnoreStart
                    $value = $property['relationValues'][0]['value'] ?? null;
                    // @codeCoverageIgnoreEnd
            }

            if ($propertyName == null || $value == "null" || $value == null || $value == '') {
                // @codeCoverageIgnoreStart
                continue; // Ignore empty properties.
                // @codeCoverageIgnoreEnd
            }

            if (is_array($value)) {
                foreach ($value as $singleValue) {
                    $this->attributes[] = new Attribute($propertyName, [$singleValue]);
                }
            } else {
                $this->attributes[] = new Attribute($propertyName, [$value]);
            }
        }
    }

    private function getPropertyGroupForPropertyName(int $propertyGroupId): string
    {
        if (!$this->propertyGroups) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        /** @var PropertyGroup[] $properties */
        if (!$propertyGroup = $this->propertyGroups->findOne(['propertyGroupId' => $propertyGroupId])) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        foreach ($propertyGroup->getNames() as $name) {
            if (strtoupper($name->getLang()) === strtoupper($this->config->getLanguage())) {
                return $name->getName();
            }
        }

        return $propertyGroup->getBackendName();
    }

    private function getPropertyValue(VariationProperty $variationProperty)
    {
        $propertyType = $variationProperty->getProperty()->getValueType();

        $value = '';

        switch ($propertyType) {
            case 'empty':
                $value = $variationProperty->getProperty()->getBackendName();
                // break omitted intentionally.
            case 'text':
                // @codeCoverageIgnoreStart
                // TODO: test when names structure becomes known
                $names = $variationProperty->getNames();
                if (!empty($names[strtoupper($this->config->getLanguage())])) {
                    $value = $names[strtoupper($this->config->getLanguage())];
                }
                break;
                // @codeCoverageIgnoreEnd
            case 'selection':
                // @codeCoverageIgnoreStart
                // TODO: test when propertySelection structure becomes known
                foreach ($variationProperty->getPropertySelection() as $selection) {
                    if (strtoupper($selection['lang']) != $this->config->getLanguage()) {
                        continue;
                    }

                    $value = $selection['name'];
                }
                break;
                // @codeCoverageIgnoreEnd
            case 'int':
                $value = $variationProperty->getValueInt();
                break;
            case 'float':
                $value = $variationProperty->getValueFloat();
                break;
            default:
                // @codeCoverageIgnoreStart
                $value = '';
                break;
                // @codeCoverageIgnoreEnd
        }

        return $value;
    }
}
