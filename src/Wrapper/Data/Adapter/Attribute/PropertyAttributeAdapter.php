<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Definition\CastType;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property as ItemVariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property as PimProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\PropertyValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup\Name as PropertyGroupName;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\MultiValueFieldAdapter;

class PropertyAttributeAdapter extends MultiValueFieldAdapter
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
        /** @var Attribute[] $attributes */
        $attributes = [];
        foreach ($variation->getProperties() as $property) {
            $propertyDetails = $this->getRegistryService()->getProperty($property->getId());
            if (!$propertyDetails) {
                continue;
            }

            if ($propertyDetails->getSkipExport()) {
                continue;
            }

            if ($propertyDetails->getType() !== ItemVariationProperty::PROPERTY_TYPE_ITEM) {
                continue;
            }

            $propertyName = null;
            /** @var Name|null $name */
            $name = Translator::translate($propertyDetails->getNames(), $this->getConfig()->getLanguage());
            if ($name) {
                $propertyName = $name->getName();
            }

            $value = $this->getPropertyValue($property);
            if (!$value && $property->getPropertyData()->getCast() === CastType::EMPTY) {
                $value = $propertyName;

                foreach ($propertyDetails->getGroups() as $group) {
                    if (!$propertyGroup = $this->getRegistryService()->getPropertyGroup($group->getId())) {
                        continue;
                    }

                    /** @var PropertyGroupName|null $name */
                    $name = Translator::translate($propertyGroup->getNames(), $this->getConfig()->getLanguage());
                    if ($name) {
                        $propertyName = $name->getName();
                    }
                }
            }

            if (Utils::isEmpty($property) || Utils::isEmpty($value)) {
                continue;
            }

            // Convert $value to an array in case it only contains a single value.
            $attributes[] = new Attribute((string)$propertyName, (array)$value);
        }

        return $attributes;
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
            case CastType::TEXT:
            case CastType::HTML:
                /** @var PropertyValue|null $propertyValue */
                $propertyValue = Translator::translate(
                    $property->getValues(),
                    $this->getConfig()->getLanguage()
                );
                if ($propertyValue) {
                    return $propertyValue->getValue();
                }

                return null;
            case CastType::SELECTION:
                if (!$property->getValues() || !$this->getRegistryService()->getPropertySelections()) {
                    return null;
                }

                $propertySelectionValues =
                    $this->getRegistryService()->getPropertySelections()->getPropertySelectionValues(
                        $property->getId(),
                        [$property->getValues()[0]],
                        $this->getConfig()->getLanguage()
                    );

                if ($propertySelectionValues) {
                    return reset($propertySelectionValues);
                }

                return null;
            case CastType::MULTI_SELECTION:
                if (!$this->getRegistryService()->getPropertySelections()) {
                    return null;
                }

                return $this->getRegistryService()->getPropertySelections()->getPropertySelectionValues(
                    $property->getId(),
                    $property->getValues(),
                    $this->getConfig()->getLanguage()
                );
            default:
                return isset($property->getValues()[0]) ? $property->getValues()[0]->getValue() : null;
        }
    }
}
