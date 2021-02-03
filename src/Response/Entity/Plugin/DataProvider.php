<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class DataProvider extends Entity
{
    /** @var string|null */
    private $key;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $description;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->key = $this->getStringProperty('key', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->description = $this->getStringProperty('description', $data);
    }

    public function getData(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description
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
}
