<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Config\FindologicConfig;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property as ItemVariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property as PimProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\PropertyValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup\Name as PropertyGroupName;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

/**
 * @property VariationEntity $variationEntity
 * @property RegistryService $registryService
 * @property FindologicConfig $config
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

            if ($propertyDetails->getSkipExport()) {
                continue;
            }

            if ($propertyDetails->getType() !== ItemVariationProperty::PROPERTY_TYPE_ITEM) {
                continue;
            }

            /** @var Name|null $name */
            $name = Translator::translate($propertyDetails->getNames(), $this->config->getLanguage());

            if (!$name || Utils::isEmpty($name->getName())) {
                continue;
            }

            $propertyName = $name->getName();
            $value = $this->getPropertyValue($property);
            if (!$value && $property->getPropertyData()->getCast() === CastType::EMPTY) {
                $value = $propertyName;

                foreach ($propertyDetails->getGroups() as $group) {
                    if (!$propertyGroup = $this->registryService->getPropertyGroup($group->getId())) {
                        continue;
                    }

                    /** @var PropertyGroupName|null $name */
                    $name = Translator::translate($propertyGroup->getNames(), $this->config->getLanguage());
                    if ($name) {
                        $propertyName = $name->getName();
                    }
                }
            }

            if (Utils::isEmpty($property) || Utils::isEmpty($value)) {
                continue;
            }

            // Convert $value to an array in case it only contains a single value.
            $this->attributes[] = new Attribute((string)$propertyName, (array)$value);
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
            case CastType::TEXT:
            case CastType::HTML:
                /** @var PropertyValue|null $propertyValue */
                $propertyValue = Translator::translate(
                    $property->getValues(),
                    $this->config->getLanguage()
                );
                if ($propertyValue) {
                    return $propertyValue->getValue();
                }

                return null;
            case CastType::SELECTION:
                if (!$property->getValues() || !$this->registryService->getPropertySelections()) {
                    return null;
                }

                $propertySelectionValues = $this->registryService->getPropertySelections()->getPropertySelectionValues(
                    $property->getId(),
                    [$property->getValues()[0]],
                    $this->config->getLanguage()
                );

                if ($propertySelectionValues) {
                    return reset($propertySelectionValues);
                }

                return null;
            case CastType::MULTI_SELECTION:
                if (!$this->registryService->getPropertySelections()) {
                    return null;
                }

                return $this->registryService->getPropertySelections()->getPropertySelectionValues(
                    $property->getId(),
                    $property->getValues(),
                    $this->config->getLanguage()
                );
            default:
                return isset($property->getValues()[0]) ? $property->getValues()[0]->getValue() : null;
        }
    }
}
