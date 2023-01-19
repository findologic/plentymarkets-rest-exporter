<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PluginConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;

class PluginConfigurationResponse extends Response implements CollectionInterface
{
    use EntityCollection;

    /** @var PluginConfiguration[] */
    private array $configurations;

    /**
     * @param PluginConfiguration[] $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @return PluginConfiguration[]
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * @return PluginConfiguration|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->configurations);
    }

    /**
     * @return PluginConfiguration[]
     */
    public function all(): array
    {
        return $this->getConfigurations();
    }

    /**
     * @return PluginConfiguration|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->configurations, $criteria);
    }

    /**
     * @return PluginConfiguration[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->configurations, $criteria);
    }
}
