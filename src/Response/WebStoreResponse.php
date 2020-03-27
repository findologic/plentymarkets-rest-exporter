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

    public function getWebStore(int $storeIdentifier): ?WebStoreEntity
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
}
