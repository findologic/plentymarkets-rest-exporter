<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;

/**
 * The registry holds response data that is relevant for general purpose, like configuration, general store data, etc.
 */
class Registry
{
    /** @var Response[] */
    private $registryData = [];

    public function set(string $key, Response $response): self
    {
        $this->registryData[$key] = $response;

        return $this;
    }

    public function get(string $key): ?Response
    {
        if (!isset($this->registryData[$key])) {
            return null;
        }

        return $this->registryData[$key];
    }
}
