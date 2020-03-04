<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;

class WebStoreResponse extends Response
{
    /** @var WebStore[] */
    private $webStores = [];

    public function parse(): void
    {
        $webstores = $this->jsonSerialize();

        foreach ($webstores as $webstore) {
            $this->webStores[$webstore['storeIdentifier']] = $webstore;
        }
    }

    /**
     * @return WebStore[]
     */
    public function getWebStores(): array
    {
        return $this->webStores;
    }

    public function getWebStore(int $storeIdentifier): ?WebStore
    {
        if (!isset($this->webStores[$storeIdentifier])) {
            return null;
        }

        return $this->webStores[$storeIdentifier];
    }
}
