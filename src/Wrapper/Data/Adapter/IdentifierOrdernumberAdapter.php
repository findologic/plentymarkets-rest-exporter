<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

class IdentifierOrdernumberAdapter extends MultiValueFieldAdapter
{
    /**
     * @return Ordernumber[]
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return Ordernumber[]
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        /** @var Ordernumber[] $ordernumbers */
        $ordernumbers = [];
        foreach ($this->getIdentifiers($variation) as $identifier) {
            $ordernumbers[] = new Ordernumber($identifier);
        }

        return $ordernumbers;
    }

    /**
     * @return string[]
     */
    private function getIdentifiers(VariationEntity $variation): array
    {
        /** @var string[] $identifiers */
        $identifiers = [];

        $identifiers[] = $this->getVariationBaseNumber($variation);
        $identifiers[] = $this->getVariationBaseModel($variation);
        $identifiers[] = $this->getVariationId($variation);
        $identifiers[] = $this->getProductId($variation);
        $identifiers = array_merge($identifiers, $this->getVariationBarcodes($variation));

        return array_unique(array_filter($identifiers));
    }

    private function getVariationBaseNumber(VariationEntity $variation): ?string
    {
        if (!$this->getConfig()->getExportOrdernumberVariantNumber()) {
            return null;
        }

        $variationBaseNumber = $variation->getBase()->getNumber();
        if (Utils::isEmpty(trim($variationBaseNumber))) {
            return null;
        }

        return $variationBaseNumber;
    }

    private function getVariationBaseModel(VariationEntity $variation): ?string
    {
        if (!$this->getConfig()->getExportOrdernumberVariantModel()) {
            return null;
        }

        $variationBaseModel = $variation->getBase()->getModel();
        if (Utils::isEmpty(trim($variationBaseModel))) {
            return null;
        }

        return $variationBaseModel;
    }

    private function getVariationId(VariationEntity $variation): ?string
    {
        if (!$this->getConfig()->getExportOrdernumberVariantId()) {
            return null;
        }

        $variationId = (string)$variation->getId();
        if (Utils::isEmpty(trim($variationId))) {
            return null;
        }

        return $variationId;
    }

    private function getProductId(VariationEntity $variation): ?string
    {
        if (!$this->getConfig()->getExportOrdernumberProductId()) {
            return null;
        }

        $variationBaseItemId = (string)$variation->getBase()->getItemId();
        if (Utils::isEmpty(trim($variationBaseItemId))) {
            return null;
        }

        return $variationBaseItemId;
    }

    private function getVariationBarcodes(VariationEntity $variation): array
    {
        if (!$this->getConfig()->getExportOrdernumberVariantBarcodes()) {
            return [];
        }

        $variationBarcodes = [];
        foreach ($variation->getBarcodes() as $barcode) {
            $variationBarcode = $barcode->getCode();
            if (Utils::isEmpty(trim($variationBarcode))) {
                continue;
            }

            $variationBarcodes[] = $variationBarcode;
        }

        return $variationBarcodes;
    }
}
