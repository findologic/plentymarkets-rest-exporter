<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;

/**
 * The registry holds response data that is relevant for general purpose, like configuration, general store data, etc.
 */
class Registry
{

    private array $registryData = [];

    /**
     * @param string $key
     * @param Entity|Response $response
     * @return $this
     */
    public function set(string $key, $response): self
    {
        $this->registryData[$key] = $response;

        return $this;
    }

    /**
     * @param string $key
     * @return Entity|Response|null
     */
    public function get(string $key)
    {
        if (!isset($this->registryData[$key])) {
            return null;
        }

        return $this->registryData[$key];
    }
}
