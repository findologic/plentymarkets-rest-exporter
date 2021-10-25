<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field;

use FINDOLOGIC\Export\Helpers\Serializable;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;

interface SingleValueField extends Field
{
    public function parseProduct(ProductEntity $product): ?Serializable;
    public function parseVariation(VariationEntity $variation): ?Serializable;
}
