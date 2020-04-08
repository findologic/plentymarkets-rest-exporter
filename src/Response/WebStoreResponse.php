<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;

class WebStoreResponse extends Response implements CollectionInterface
{
    /** @var WebStore[] */
    private $webStores = [];

    /**
     * @param WebStore[] $webStores
     */
    public function __construct(array $webStores)
    {
        $this->webStores = $webStores;
    }

    /**
     * @return WebStore[]
     */
    public function getWebStores(): array
    {
        return $this->webStores;
    }

    public function getWebStoreByStoreIdentifier(int $storeIdentifier): ?WebStore
    {
        return $this->findOne(['storeIdentifier' => $storeIdentifier]);
    }

    /**
     * @return WebStore
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

    /**
     * @return WebStore|null
     */
    public function findOne(array $criteria): ?Entity
    {
        foreach ($this->webStores as $webStore) {
            foreach ($criteria as $criterion => $value) {
                $getter = 'get' . ucfirst($criterion);

                if ($webStore->{$getter}() !== $value) {
                    continue 2;
                }

                return $webStore;
            }
        }

        return null;
    }

    public function find(array $criteria): array
    {
        $webStoresMatchingCriteria = [];
        foreach ($this->webStores as $webStore) {
            foreach ($criteria as $criterion => $value) {
                $getter = 'get' . ucfirst($criterion);

                if ($webStore->{$getter}() !== $value) {
                    continue 2;
                }

                $webStoresMatchingCriteria[] = $webStore;
            }
        }

        return $webStoresMatchingCriteria;
    }
}
