<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;

abstract class Wrapper
{
    /**
     * @param int $start
     * @param int $total
     * @param ItemResponse $products
     * @param PimVariationResponse $variations
     * @param PropertySelectionResponse|null $propertySelection
     */
    abstract public function wrap(
        int $start,
        int $total,
        ItemResponse $products,
        PimVariationResponse $variations,
        ?PropertySelectionResponse $propertySelection
    ): void;

    abstract public function getExportPath(): string;
}
