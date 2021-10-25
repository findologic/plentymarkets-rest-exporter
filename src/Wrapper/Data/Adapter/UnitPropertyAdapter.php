<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit\Name as UnitName;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Property;

class UnitPropertyAdapter extends SingleValueFieldAdapter
{
    public function adapt(ProductEntity $product): ?Property
    {
        return null;
    }

    public function adaptVariation(VariationEntity $variation): ?Property
    {
        if (!$unitData = $variation->getUnit()) {
            return null;
        }

        if (!$unitEntity = $this->getRegistryService()->getUnit($unitData->getUnitId())) {
            return null;
        }

        /** @var UnitName|null $name */
        if (!$name = Translator::translate($unitEntity->getNames(), $this->getConfig()->getLanguage())) {
            return null;
        }

        $property = new Property('base_unit');
        $property->addValue($name->getName());

        return $property;
    }
}
