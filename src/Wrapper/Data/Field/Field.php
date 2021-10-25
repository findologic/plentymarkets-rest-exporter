<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;

interface Field
{
    public function parseProduct(ProductEntity $product);
    public function parseVariation(VariationEntity $variation);

    /**
     * Resets all parsed data.
     */
    public function reset(): void;
}
