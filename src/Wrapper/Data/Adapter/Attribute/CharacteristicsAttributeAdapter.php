<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty\Name as ItemPropertyName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Characteristic;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\CharacteristicSelection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\CharacteristicText;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\MultiValueFieldAdapter;

class CharacteristicsAttributeAdapter extends MultiValueFieldAdapter
{
    /**
     * @return Attribute[]
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return Attribute[]
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        $attributes = [];
        foreach ($variation->getBase()->getCharacteristics() as $characteristic) {
            $itemProperty = $this->getRegistryService()->getItemProperty($characteristic->getPropertyId());
            if (!$this->shouldProcessCharacteristic($itemProperty)) {
                continue;
            }

            $attribute = $this->processCharacteristic($itemProperty, $characteristic);
            if (!$attribute) {
                continue;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }

    private function processCharacteristic(ItemProperty $itemProperty, Characteristic $characteristic): ?Attribute
    {
        if ($itemProperty->getValueType() === CastType::EMPTY) {
            $name = $this->getTranslatedPropertyGroupName($itemProperty->getPropertyGroupId());
            $value = $this->getCharacteristicValue($itemProperty, $characteristic);

            if (Utils::isEmpty($name) || Utils::isEmpty($value)) {
                return null;
            }

            return new Attribute($name, (array)$value);
        }

        $value = $this->getCharacteristicValue($itemProperty, $characteristic);
        if (Utils::isEmpty($value)) {
            $name = $this->getTranslatedPropertyGroupName($itemProperty->getPropertyGroupId());
            $value = $this->getCharacteristicName($itemProperty, $itemProperty->getBackendName());

            if (Utils::isEmpty($name) || Utils::isEmpty($value)) {
                return null;
            }

            return new Attribute($name, (array)$value);
        }

        $name = $this->getCharacteristicName($itemProperty, $itemProperty->getBackendName());
        if (Utils::isEmpty($name) || Utils::isEmpty($value)) {
            return null;
        }

        return new Attribute($name, (array)$value);
    }

    private function shouldProcessCharacteristic(?ItemProperty $itemProperty): bool
    {
        if (!$itemProperty) {
            return false;
        }

        if (!$itemProperty->isSearchable()) {
            return false;
        }

        if ($itemProperty->getValueType() === CastType::EMPTY && !$itemProperty->getPropertyGroupId()) {
            return false;
        }

        return true;
    }

    private function getCharacteristicValue(ItemProperty $variationProperty, Characteristic $characteristic)
    {
        $propertyType = $variationProperty->getValueType();

        switch ($propertyType) {
            case CastType::EMPTY:
                return $variationProperty->getBackendName();
            case CastType::TEXT:
                /** @var CharacteristicText|null $text */
                $text = Translator::translate($characteristic->getValueTexts(), $this->getConfig()->getLanguage());
                if ($text) {
                    return $text->getValue();
                }

                return null;
            case CastType::SELECTION:
                /** @var CharacteristicSelection|null $selection */
                $selection = Translator::translate(
                    $characteristic->getPropertySelections(),
                    $this->getConfig()->getLanguage()
                );
                if ($selection) {
                    return $selection->getName();
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

    private function getCharacteristicName(
        ItemProperty $itemProperty,
        ?string $default
    ): ?string {
        /** @var ItemPropertyName $name */
        $name = Translator::translate($itemProperty->getNames(), $this->getConfig()->getLanguage());
        if ($name) {
            return $name->getName();
        }

        return $default;
    }

    private function getTranslatedPropertyGroupName(?int $propertyGroupId): ?string
    {
        $propertyGroup = $this->getPropertyGroup($propertyGroupId);
        if (!$propertyGroup) {
            return null;
        }

        /** @var Name|null $name */
        $name = Translator::translate($propertyGroup->getNames(), $this->getConfig()->getLanguage());
        if ($name) {
            return $name->getName();
        }

        return $propertyGroup->getBackendName();
    }

    private function getPropertyGroup(?int $propertyGroupId): ?ItemPropertyGroup
    {
        if (!$propertyGroupId) {
            return null;
        }

        return $this->getRegistryService()->getItemPropertyGroup($propertyGroupId);
    }
}
