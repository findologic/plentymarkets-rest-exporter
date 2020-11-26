<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Characteristic;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

/**
 * Note: ItemProperties are synonymous with characteristics. From a route perspective they reference the same
 * entity, but return different details.
 *
 * @property VariationEntity $variationEntity
 * @property RegistryService $registryService
 * @property Config $config
 * @property Attribute[] $attributes
 */
trait CharacteristicAware
{
    public function processCharacteristics(): void
    {
        foreach ($this->variationEntity->getBase()->getCharacteristics() as $characteristic) {
            $itemProperty = $this->registryService->getItemProperty($characteristic->getPropertyId());
            if (!$this->shouldProcessCharacteristic($itemProperty)) {
                continue;
            }

            $attribute = $this->processCharacteristic($itemProperty, $characteristic);
            if (!$attribute) {
                continue;
            }

            $this->attributes[] = $attribute;
        }
    }

    protected function processCharacteristic(ItemProperty $itemProperty, Characteristic $characteristic): ?Attribute
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

    protected function shouldProcessCharacteristic(?ItemProperty $itemProperty): bool
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

    protected function getCharacteristicValue(ItemProperty $variationProperty, Characteristic $characteristic)
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

    protected function getCharacteristicName(
        ItemProperty $itemProperty,
        ?string $default
    ): ?string {
        foreach ($itemProperty->getNames() as $name) {
            if ($name->getLang() !== $this->config->getLanguage()) {
                continue;
            }

            return $name->getName();
        }

        return $default;
    }

    protected function getTranslatedPropertyGroupName(?int $propertyGroupId): ?string
    {
        if (!$propertyGroupId || !$propertyGroup = $this->registryService->getPropertyGroup($propertyGroupId)) {
            return null;
        }

        foreach ($propertyGroup->getNames() as $name) {
            if (strtoupper($name->getLang()) === strtoupper($this->config->getLanguage())) {
                return $name->getName();
            }
        }

        return $propertyGroup->getBackendName();
    }
}
