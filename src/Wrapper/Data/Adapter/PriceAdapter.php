<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\InsteadPrice;

class PriceAdapter extends MultiValueFieldAdapter
{
    /**
     * @return array<int, Price|Property>
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return array<int, Price|InsteadPrice>
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        $price = 0.0;
        $insteadPrice = 0.0;
        foreach ($variation->getSalesPrices() as $variationSalesPrice) {
            $salesPrice = $variationSalesPrice->getPrice();
            if (!$this->isPriceAboveZero($salesPrice)) {
                continue;
            }

            switch ($variationSalesPrice->getId()) {
                case $this->getRegistryService()->getPriceId():
                    $price = Utils::getLowestFloatValue($price, $salesPrice);
                    break;
                case $this->getRegistryService()->getRrpId():
                    $insteadPrice = $salesPrice;
                    break;
                default:
                    break;
            }
        }

        $exportPrice = new Price();
        $exportPrice->setValue($price);

        $exportInsteadPrice = new InsteadPrice();
        $exportInsteadPrice->setValue($insteadPrice);

        return [
            $exportPrice,
            $exportInsteadPrice
        ];
    }

    private function isPriceAboveZero(float $price): bool
    {
        return $price > 0;
    }
}
