<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\SingleValueFieldAdapter;

class ManufacturerAttributeAdapter extends SingleValueFieldAdapter
{
    public function adapt(ProductEntity $product): ?Attribute
    {
        $manufacturerId = $product->getManufacturerId();
        if (Utils::isEmpty($manufacturerId)) {
            return null;
        }

        $manufacturer = $this->getRegistryService()->getManufacturer($manufacturerId);
        if (Utils::isEmpty($manufacturer->getName())) {
            return null;
        }

        return new Attribute('vendor', [$manufacturer->getName()]);
    }

    public function adaptVariation(VariationEntity $variation): ?Attribute
    {
        return null;
    }
}
