<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Helpers\Serializable;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;

abstract class SingleValueFieldAdapter extends AbstractFieldAdapter
{
    abstract public function adapt(ProductEntity $product): ?Serializable;
    abstract public function adaptVariation(VariationEntity $variation): ?Serializable;
}
