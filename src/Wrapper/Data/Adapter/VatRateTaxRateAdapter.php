<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\TaxRate;

class VatRateTaxRateAdapter extends SingleValueFieldAdapter
{
    public function adapt(ProductEntity $product): ?TaxRate
    {
        return null;
    }

    public function adaptVariation(VariationEntity $variation): ?TaxRate
    {
        foreach ($this->getRegistryService()->getStandardVat()->getVatRates() as $vatRateEntity) {
            if ($vatRateEntity->getId() !== $variation->getBase()->getVatId()) {
                continue;
            }

            $vatRate = $vatRateEntity->getVatRate();
            $taxRate = new TaxRate();
            $taxRate->setValue($vatRate);

            return $taxRate;
        }

        return null;
    }
}
