<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Data\Usergroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property as ItemVariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Characteristic;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property as PimProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

class Variation
{
    /** @var Config */
    private $config;

    /** @var RegistryService */
    private $registryService;

    /** @var PimVariation */
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
    private $price = 0.0;

    /** @var float */
    private $insteadPrice = 0.0;

    /** @var array */
    private $prices = [];

    /** @var Attribute[] */
    private $attributes = [];

    /** @var Property[] */
    private $properties = [];

    /** @var Usergroup[] */
    private $groups = [];

    /** @var Keyword[] */
    private $tags = [];

    /** @var int|null */
    private $salesRank;

    /** @var Image */
    private $image;

    public function __construct(
        Config $config,
        RegistryService $registryService,
        PimVariation $variationEntity
    ) {
        $this->config = $config;
        $this->variationEntity = $variationEntity;
        $this->registryService = $registryService;
    }

    public function processData(): void
    {
        $this->isMain = $this->variationEntity->getBase()->isMain();
        $this->position = $this->variationEntity->getBase()->getPosition();
        $this->vatId = $this->variationEntity->getBase()->getVatId();

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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    private function processIdentifiers(): void
    {
        $this->number = $this->variationEntity->getBase()->getNumber();
        $this->model = $this->variationEntity->getBase()->getModel();
        $this->id = $this->variationEntity->getId();
        $this->itemId = $this->variationEntity->getBase()->getItemId();

        foreach ($this->variationEntity->getBarcodes() as $barcode) {
            $this->barcodes[] = $barcode->getCode();
        }
    }

    private function processCategories(): void
    {
        $variationCategories = $this->variationEntity->getCategories();
        foreach ($variationCategories as $variationCategory) {
            $category = $this->registryService->getCategory($variationCategory->getId());

            if (!$category) {
                continue;
            }

            foreach ($category->getDetails() as $categoryDetail) {
                if (strtoupper($categoryDetail->getLang()) !== strtoupper($this->config->getLanguage())) {
                    continue;
                }

                $this->attributes[] = new Attribute('cat', [$this->buildCategoryPath($category)]);
                $this->attributes[] = new Attribute(
                    'cat_url',
                    [parse_url($categoryDetail->getPreviewUrl(), PHP_URL_PATH)]
                );
            }
        }
    }

    private function buildCategoryPath(Category $category): string
    {
        $path = [];
        foreach ($category->getDetails() as $categoryDetail) {
            if (strtoupper($categoryDetail->getLang()) !== strtoupper($this->config->getLanguage())) {
                continue;
            }

            if ($category->getParentCategoryId() !== null) {
                $path[] = $this->buildCategoryPath(
                    $this->registryService->getCategory($category->getParentCategoryId())
                );
            }

            $path[] = $categoryDetail->getName();
        }

        return implode('_', $path);
    }

    private function processPrices(): void
    {
        $priceId = $this->registryService->getPriceId();
        $insteadPriceId = $this->registryService->getRrpId();

        $priceIdProperty = new Property('price_id');
        $priceIdProperty->addValue((string)$priceId);
        $this->properties[] = $priceIdProperty;

        foreach ($this->variationEntity->getSalesPrices() as $variationSalesPrice) {
            $price = $variationSalesPrice->getPrice();
            if ($variationSalesPrice->getPrice() == 0) {
                continue;
            }

            if ($variationSalesPrice->getId() === $priceId) {
                // Always take the lowest price.
                if ($price < $this->price || $this->price === 0.0) {
                    $this->price = $price;
                }
            }

            if ($variationSalesPrice->getId() === $insteadPriceId) {
                $this->insteadPrice = $price;
            }
        }
    }

    private function processAttributes(): void
    {
        foreach ($this->variationEntity->getAttributeValues() as $variationAttributeValue) {
            $attribute = $this->registryService->getAttribute($variationAttributeValue->getId());
            if (!$attribute) {
                continue;
            }

            $emptyName = Utils::isEmpty($attribute->getBackendName());
            if ($emptyName || Utils::isEmpty($variationAttributeValue->getValue()->getBackendName())) {
                continue;
            }

            $this->attributes[] = new Attribute(
                $attribute->getBackendName(),
                [$variationAttributeValue->getValue()->getBackendName()]
            );
        }
    }

    private function processGroups(): void
    {
        $stores = $this->registryService->getAllWebStores();
        $variationClients = $this->variationEntity->getClients();
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
            $tagIds[] = $tag->getId();

            $tagName = $tag->getTagData()->getName();

            foreach ($tag->getTagData()->getNames() as $translatedTag) {
                if ($translatedTag->getLang() === strtolower($this->config->getLanguage())) {
                    $tagName = $translatedTag->getName();

                    break;
                }
            }

            $this->tags[] = new Keyword($tagName);
        }

        if ($tagIds) {
            $this->attributes[] = new Attribute('cat_id', $tagIds);
        }
    }

    private function processImages(): void
    {
        $images = array_merge($this->variationEntity->getImages(), $this->variationEntity->getBase()->getImages());

        foreach ($images as $image) {
            $imageAvailabilities = $image->getAvailabilities();
            foreach ($imageAvailabilities as $imageAvailability) {
                if ($imageAvailability->getType() === Availability::STORE) {
                    $this->image = new Image($image->getUrlMiddle());

                    return;
                }
            }
        }
    }

    private function processCharacteristics(): void
    {
        $characteristics = $this->variationEntity->getCharacteristics();

        foreach ($characteristics as $characteristic) {
            // ItemProperties is synonymous with characteristics. From a route perspective each of them contain
            // different data that is important for us.
            $itemProperty = $this->registryService->getItemProperty($characteristic->getId());

            if ($itemProperty && !$itemProperty->isSearchable()) {
                continue;
            }

            if ($itemProperty->getValueType() === CastType::EMPTY && !$itemProperty->getPropertyGroupId()) {
                continue;
            }

            $value = $this->getCharacteristicValue($itemProperty, $characteristic);
            // If there is no value for the property, use its name as value and the group name as the property name.
            // Properties of type "empty" are a special case since they never have a value of their own.
            if ($itemProperty->getValueType() === CastType::EMPTY) {
                $propertyName = $this->getPropertyGroupForPropertyName($itemProperty->getPropertyGroupId());
            } elseif ($value === '') {
                $propertyName = $this->getPropertyGroupForPropertyName($itemProperty->getPropertyGroupId());
                $value = $this->getCharacteristicName($characteristic, $itemProperty->getBackendName());
            } else {
                $propertyName = $this->getCharacteristicName($characteristic, $itemProperty->getBackendName());
            }

            if (Utils::isEmpty($propertyName) || Utils::isEmpty($value)) {
                continue;
            }

            $this->attributes[] = new Attribute($propertyName, (array)$value);
        }
    }

    private function getCharacteristicName(Characteristic $characteristic, ?string $default): ?string
    {
        foreach ($characteristic->getValueTexts() as $text) {
            if ($text->getLang() !== $this->config->getLanguage()) {
                continue;
            }

            return $text->getValue();
        }

        return $default;
    }

    private function processProperties(): void
    {
        foreach ($this->variationEntity->getProperties() as $property) {
            $propertyDetails = $this->registryService->getProperty($property->getId());
            if (!$propertyDetails) {
                continue;
            }

            if ($propertyDetails->getTypeIdentifier() !== ItemVariationProperty::PROPERTY_TYPE_ITEM) {
                continue;
            }

            $propertyName = null;
            foreach ($propertyDetails->getNames() as $name) {
                if (strtoupper($name->getLang()) === strtoupper($this->config->getLanguage())) {
                    $propertyName = $name->getName();
                }
            }

            $value = $this->getPropertyValue($property);

            if (Utils::isEmpty($property) || Utils::isEmpty($value)) {
                continue;
            }

            // Convert $value to an array in case it only contains a single value.
            $this->attributes[] = new Attribute($propertyName, (array)$value);
        }
    }

    /**
     * @param PimProperty $property
     * @return string[]|string|null
     */
    private function getPropertyValue(PimProperty $property)
    {
        switch ($property->getPropertyData()->getCast()) {
            case CastType::EMPTY:
                return null;
            case CastType::SHORT_TEXT:
            case CastType::LONG_TEXT:
                foreach ($property->getPropertyData()->getNames() as $name) {
                    if (strtoupper($name->getLang()) !== strtoupper($this->config->getLanguage())) {
                        continue;
                    }

                    return $name->getValue();
                }

                return null;
            case CastType::SELECTION:
                foreach ($property->getPropertyData()->getSelections() as $selection) {
                    foreach ($selection->getRelation()->getValues() as $relationValue) {
                        if (strtoupper($relationValue->getLang()) !== strtoupper($this->config->getLanguage())) {
                            continue;
                        }

                        return $relationValue->getValue();
                    }
                }

                return null;
            case CastType::MULTI_SELECTION:
                return $this->registryService->getPropertySelections()->getPropertySelectionValues(
                    $property->getId(),
                    $this->config->getLanguage()
                );
            default:
                return $property->getPropertyData()->getNames()[0]->getValue() ?? null;
        }
    }

    private function getCharacteristicValue(ItemProperty $variationProperty, Characteristic $characteristic)
    {
        $propertyType = $variationProperty->getValueType();

        switch ($propertyType) {
            case CastType::EMPTY:
                return $variationProperty->getBackendName();
            case CastType::TEXT:
                foreach ($characteristic->getValueTexts() as $text) {
                    if (strtoupper($text->getLang()) === strtoupper($this->config->getLanguage())) {
                        return $text->getValue();
                    }
                }

                return null;
            case CastType::SELECTION:
                foreach ($characteristic->getPropertySelections() as $selection) {
                    if (strtoupper($selection->getLang()) === strtoupper($this->config->getLanguage())) {
                        return $selection->getName();
                    }
                }

                return null;
            case CastType::INT:
                return $characteristic->getValueInt();
            case CastType::FLOAT:
                return $characteristic->getValueFloat();
            default:
                return null;
        }
    }

    private function getPropertyGroupForPropertyName(?int $propertyGroupId): string
    {
        if (!$propertyGroupId) {
            return '';
        }

        $propertyGroup = $this->registryService->getPropertyGroup($propertyGroupId);
        foreach ($propertyGroup->getNames() as $name) {
            if (strtoupper($name->getLang()) === strtoupper($this->config->getLanguage())) {
                return $name->getName();
            }
        }

        return $propertyGroup->getBackendName();
    }
}
