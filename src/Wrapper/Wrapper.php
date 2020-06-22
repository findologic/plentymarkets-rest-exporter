<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Product as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Variation;

abstract class Wrapper
{
    /**
     * @param int $start
     * @param int $total
     * @param ProductEntity[] $products
     * @param Variation[] $variations
     */
    abstract public function wrap(
        int $start,
        int $total,
        array $products,
        array $variations
    ): void;
}
