<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;

class PluginFromSetResponse extends Response implements CollectionInterface
{
    use EntityCollection;

    /** @var Plugin[] */
    private $plugins = [];

    /**
     * @param Plugin[] $plugins
     */
    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @return Plugin|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->plugins);
    }

    public function all(): array
    {
        return $this->getPlugins();
    }

    /**
     * @return Plugin|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->plugins, $criteria);
    }

    /**
     * @return Plugin[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->plugins, $criteria);
    }
}
