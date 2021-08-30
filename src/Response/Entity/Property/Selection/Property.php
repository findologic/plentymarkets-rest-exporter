<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Property extends Entity
{
    private ?int $id;

    private ?string $cast;

    private ?string $typeIdentifier;

    private ?int $position;

    private ?int $propertyId;

    private ?int $propertyGroupId;

    private ?string $createdAt;

    private ?string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = $this->getIntProperty('id', $data);
        $this->cast = $this->getStringProperty('cast', $data);
        $this->typeIdentifier = $this->getStringProperty('typeIdentifier', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->propertyId = $this->getIntProperty('propertyId', $data);
        $this->propertyGroupId = $this->getIntProperty('propertyGroupId', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'cast' => $this->cast,
            'typeIdentifier' => $this->typeIdentifier,
            'position' => $this->position,
            'propertyId' => $this->propertyId,
            'propertyGroupId' => $this->propertyGroupId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCast(): ?string
    {
        return $this->cast;
    }

    public function getTypeIdentifier(): ?string
    {
        return $this->typeIdentifier;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getPropertyId(): ?int
    {
        return $this->propertyId;
    }

    public function getPropertyGroupId(): ?int
    {
        return $this->propertyGroupId;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
