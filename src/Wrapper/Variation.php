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
        $this->processCharacteristics();
        $this->processProperties();
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

    public function getPrice(): ?float
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
                continue;
            }

            $tagIds[] = $tag->getTagId();

            $translatedTagName = $tag->getTag()->getTagName();

            foreach ($tag->getTag()->getNames() as $tagName) {
                if ($tagName->getTagLang() === strtolower($this->config->getLanguage())) {
                    $translatedTagName = $tagName->getTagName();

                    break;
                }
            }

            $this->tags[] = new Keyword($translatedTagName);
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

    private function processCharacteristics(): void
    {
        $characteristics = $this->variationEntity->getVariationProperties();

        foreach ($characteristics as $characteristic) {
            if (!$characteristicProperty = $characteristic->getProperty()) {
                continue;
            }

            if (!$characteristicProperty->isSearchable()) {
                continue;
            }

            if ($characteristicProperty->getValueType() === 'empty' && !$characteristicProperty->getPropertyGroupId()) {
                continue;
            }

            $value = $this->getPropertyValue($characteristic);
            // If there is no value for the property, use its name as value and the group name as the property name.
            // Properties of type "empty" are a special case since they never have a value of their own.
            if ($characteristicProperty->getValueType() === 'empty') {
                $propertyName = $this->getPropertyGroupForPropertyName($characteristicProperty->getPropertyGroupId());
            } elseif ($value === '') {
                $propertyName = $this->getPropertyGroupForPropertyName($characteristicProperty->getPropertyGroupId());
                $value = $characteristic->getPropertyName($this->config->getLanguage());
            } else {
                $propertyName = $characteristic->getPropertyName($this->config->getLanguage());
            }

            if ($propertyName != null && $value != "null" && $value != null && $value != '') {
                $this->attributes[] = new Attribute($propertyName, [$value]);
            }
        }
    }

    private function processProperties(): void
    {
        /** @var PropertyResponse $properties */
        $properties = $this->registry->get('properties');

        foreach ($this->variationEntity->getProperties() as $property) {
            if ($property->getRelationTypeIdentifier() != ItemVariationProperty::PROPERTY_TYPE_ITEM) {
                continue;
            }

            $propertyName = $properties->getPropertyName($property->getPropertyId(), $this->config->getLanguage());

            $value = null;

            switch ($property->getPropertyRelation()->getCast()) {
                case 'empty':
                    $value = $propertyName;
                    $propertyName = $this->getPropertyNameOfEmptyProperty($property, $properties);
                    break;
                case 'shortText':
                case 'longText':
                    $value = $this->getTextPropertyValue($property);
                    break;
                case 'selection':
                    $value = $this->getSelectionPropertyValue($property, $properties);
                    break;
                case 'multiSelection':
                    $value = $this->getMultiselectionPropertyValue($property);
                    break;
                default:
                    $relationValues = $property->getRelationValues();
                    $value = count($relationValues) ? $relationValues[0]->getValue() : null;
            }

            if ($propertyName == null || $value == "null" || $value == null || $value == '') {
                continue;
            }

            $this->attributes[] = new Attribute($propertyName, (array)$value);
        }
    }

    private function getPropertyGroupForPropertyName(int $propertyGroupId): ?string
    {
        /** @var PropertyGroup[] $properties */
        if (!$propertyGroup = $this->propertyGroups->findOne(['id' => $propertyGroupId])) {
            return null;
        }

        foreach ($propertyGroup->getNames() as $name) {
            if (strtoupper($name->getLang()) === strtoupper($this->config->getLanguage())) {
                return $name->getName();
            }
        }

        return $propertyGroup->getBackendName();
    }

    /**
     * @return float|int|string|null
     */
    private function getPropertyValue(VariationProperty $variationProperty)
    {
        $propertyType = $variationProperty->getProperty()->getValueType();

        $value = '';

        switch ($propertyType) {
            case 'empty':
                $value = $variationProperty->getProperty()->getBackendName();
                // break omitted intentionally.
            case 'text':
                $names = $variationProperty->getNames();
                foreach ($names as $name) {
                    if (strtoupper($name->getLang()) == strtoupper($this->config->getLanguage())) {
                        $value = $name->getValue();
                        break;
                    }
                }
                break;
            case 'selection':
                foreach ($variationProperty->getPropertySelection() as $selection) {
                    if (strtoupper($selection->getLang()) == strtoupper($this->config->getLanguage())) {
                        $value = $selection->getName();
                        break;
                    }
                }
                break;
            case 'int':
                $value = $variationProperty->getValueInt();
                break;
            case 'float':
                $value = $variationProperty->getValueFloat();
                break;
            default:
                $value = '';
                break;
        }

        return $value;
    }

    private function getPropertyNameOfEmptyProperty(ItemVariationProperty $property, PropertyResponse $properties): ?string
    {
        return $properties->getPropertyGroupName(
            $property->getPropertyId(),
            $this->config->getLanguage()
        );
    }

    public function getTextPropertyValue(ItemVariationProperty $property): ?string
    {
        $value = null;

        foreach ($property->getRelationValues() as $relationValue) {
            if (strtoupper($relationValue->getLang()) != strtoupper($this->config->getLanguage())) {
                continue;
            }

            $value = $relationValue->getValue();
        }

        return $value;
    }

    public function getSelectionPropertyValue(ItemVariationProperty $property, PropertyResponse $properties): ?string
    {
        if (!$relationValues = $property->getRelationValues()) {
            return null;
        }

        $selectionId = (int)reset($relationValues)->getValue();
        return $properties->getPropertySelectionValue(
            $property->getPropertyId(),
            $selectionId,
            $this->config->getLanguage()
        );
    }

    public function getMultiselectionPropertyValue(ItemVariationProperty $property): array
    {
        return $this->propertySelections->getPropertySelectionValues(
            $property->getPropertyId(),
            $this->config->getLanguage()
        );
    }
}
