<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class WebStore extends Entity
{
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string */
    private $storeIdentifier;

    /** @var string */
    private $name;

    /** @var int */
    private $pluginSetId;

    /** @var array */
    private $configuration;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->type = (string)$data['type'];
        $this->storeIdentifier = (int)$data['storeIdentifier'];
        $this->name = (string)$data['name'];
        $this->pluginSetId = (int)$data['pluginSetId'];
        $this->configuration = (array)$data['configuration'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStoreIdentifier(): string
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

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'storeIdentifier' => $this->storeIdentifier,
            'name' => $this->name,
            'pluginSetId' => $this->pluginSetId,
            'configuration' => $this->configuration
        ];
    }
}
