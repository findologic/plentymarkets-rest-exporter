<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation;

class Selection extends Entity
{
    private int $id;

    private int $propertyId;

    private int $position;

    private string $createdAt;

    private string $updatedAt;

    private Relation $relation;

    private Property $property;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyId = (int)$data['propertyId'];
        $this->position = (int)$data['position'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['relation'])) {
            $this->relation = new Relation($data['relation']);
        }

        if (!empty($data['property'])) {
            $this->property = new Property($data['property']);
        }
    }

    public function getData(): array
    {
        $data = [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];

        if ($this->relation) {
            $data['relation'] = $this->relation->getData();
        }

        if ($this->property) {
            $data['property'] = $this->property->getData();
        }

        return $data;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }
}
