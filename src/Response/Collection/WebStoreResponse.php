<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;

class WebStoreResponse extends Response implements CollectionInterface
{
    use EntityCollection;

    /** @var WebStore[] */
    private array $webStores;

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
     * @return WebStore|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->webStores);
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
        return $this->findOneEntityByCriteria($this->webStores, $criteria);
    }

    /**
     * @return WebStore[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->webStores, $criteria);
    }
}
