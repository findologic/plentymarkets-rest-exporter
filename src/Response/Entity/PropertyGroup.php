<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup\Name;

class PropertyGroup extends Entity
{
    private ?int $id;

    private ?int $position;

    private ?string $createdAt;

    private ?string $updatedAt;

    /** @var Name[] */
    private array $names = [];

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);
        $this->names = $this->getEntities(Name::class, 'names', $data);
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        $data =  [
            'id' => $this->id,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'names' => $names
        ];

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        return $this->names;
    }
}
