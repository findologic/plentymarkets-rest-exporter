<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Helpers\Serializable;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Property;

class VariationIdPropertyAdapter extends SingleValueFieldAdapter
{
    public function adapt(ProductEntity $product): ?Property
    {
        return null;
    }

    public function adaptVariation(VariationEntity $variation): ?Property
    {
        $id = $variation->getId();
        $property = new Property('variation_id');
        $property->addValue((string)$id);

        return $property;
    }
}
