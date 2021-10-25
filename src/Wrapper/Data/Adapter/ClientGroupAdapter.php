<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Data\Usergroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;

class ClientGroupAdapter extends MultiValueFieldAdapter
{
    /**
     * @return Usergroup[]
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return Usergroup[]
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        $stores = $this->getRegistryService()->getAllWebStores();
        /** @var Usergroup[] $usergroups */
        $usergroups = [];
        foreach ($variation->getClients() as $variationClient) {
            if ($store = $stores->getWebStoreByStoreIdentifier($variationClient->getPlentyId())) {
                $usergroups[] = new Usergroup($store->getId() . '_');
            }
        }

        return $usergroups;
    }
}
