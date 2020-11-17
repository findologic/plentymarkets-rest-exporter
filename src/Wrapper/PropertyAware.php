<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property as ItemVariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property as PimProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

/**
 * @property VariationEntity $variationEntity
 * @property RegistryService $registryService
 * @property Config $config
 * @property Attribute[] $attributes
 */
trait PropertyAware
{
    public function processProperties(): void
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
    protected function getPropertyValue(PimProperty $property)
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
                if (!$this->registryService->getPropertySelections()) {
                    return null;
                }

                return $this->registryService->getPropertySelections()->getPropertySelectionValues(
                    $property->getId(),
                    $this->config->getLanguage()
                );
            default:
                return $property->getPropertyData()->getNames()[0]->getValue() ?? null;
        }
    }
}
