<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Container extends Entity
{
    private ?string $key;
    private ?string $name;
    private ?string $description;
    private ?bool $multiple;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->key = $this->getStringProperty('key', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->description = $this->getStringProperty('description', $data);
        $this->multiple = $this->getBoolProperty('multiple', $data);
    }

    public function getData(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'multiple' => $this->multiple
        ];
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMultiple(): ?bool
    {
        return $this->multiple;
    }
}
