<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration;

class WebStore extends Entity
{
    private int $id;
    private string $type;
    private int $storeIdentifier;
    private string $name;
    private int $pluginSetId;
    private Configuration $configuration;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->type = (string)$data['type'];
        $this->storeIdentifier = (int)$data['storeIdentifier'];
        $this->name = (string)$data['name'];
        $this->pluginSetId = (int)$data['pluginSetId'];
        $this->configuration = new Configuration($data['configuration']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStoreIdentifier(): int
    {
        return $this->storeIdentifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPluginSetId(): int
    {
        return $this->pluginSetId;
    }

    public function getConfiguration(): Configuration
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->configuration;
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'storeIdentifier' => $this->storeIdentifier,
            'name' => $this->name,
            'pluginSetId' => $this->pluginSetId,
            'configuration' => $this->configuration->getData()
        ];
    }
}
