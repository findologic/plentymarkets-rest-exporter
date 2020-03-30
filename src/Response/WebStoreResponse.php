<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStoreEntity;

class WebStoreResponse extends Response implements CollectionInterface
{
    /** @var WebStoreEntity[] */
    private $webStores = [];

    public function parse(): void
    {
        $webstores = $this->jsonSerialize();

        foreach ($webstores as $webstore) {
            $this->webStores[$webstore['storeIdentifier']] = $webstore;
        }
    }

    /**
     * @return WebStoreEntity[]
     */
    public function getWebStores(): array
    {
        return $this->webStores;
    }

    public function getWebStoreByStoreIdentifier(int $storeIdentifier): ?WebStoreEntity
    {
        if (!isset($this->webStores[$storeIdentifier])) {
            return null;
        }

        return $this->webStores[$storeIdentifier];
    }

    /**
     * @return WebStoreEntity
     */
    public function first(): Entity
    {
        $webStores = array_values($this->webStores);

        if (!isset($webStores[0])) {
            return null;
        }

        return $webStores[0];
    }

    public function all(): array
    {
        return $this->getWebStores();
    }

    public function findOne(array $criteria): ?Entity
    {
        foreach ($this->webStores as $webStore) {
            foreach ($criteria as $criterion => $value) {
                if ($webStore[$criterion] !== $value) {
                    continue 2;
                }

                return $webStore;
            }
        }

        return null;
    }
}
