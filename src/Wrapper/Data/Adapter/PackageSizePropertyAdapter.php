<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Helpers\Serializable;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Property;

class PackageSizePropertyAdapter extends SingleValueFieldAdapter
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

        $packageSize = $unitData->getContent();
        $property = new Property('package_size');
        $property->addValue($packageSize);

        return $property;
    }
}
