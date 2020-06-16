<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation\RelationValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Property extends Entity
{
    private int $id;

    private string $cast;

    private string $typeIdentifier;

    private int $position;

    private string $createdAt;

    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->cast = (string)$data['cast'];
        $this->typeIdentifier = (string)$data['typeIdentifier'];
        $this->position = (int)$data['position'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'cast' => $this->cast,
            'typeIdentifier' => $this->typeIdentifier,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCast(): string
    {
        return $this->cast;
    }

    public function getTypeIdentifier(): string
    {
        return $this->typeIdentifier;
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
}
