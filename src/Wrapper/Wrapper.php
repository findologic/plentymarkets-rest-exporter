<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;

abstract class Wrapper
{
    /**
     * @param int $start
     * @param int $total
     * @param ItemResponse $products
     * @param ItemVariationResponse $variations
     */
    abstract public function wrap(
        int $start,
        int $total,
        ItemResponse $products,
        ItemVariationResponse $variations
    ): void;

    abstract public function getExportPath(): string;
}
