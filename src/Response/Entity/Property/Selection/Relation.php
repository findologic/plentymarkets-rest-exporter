<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation\RelationValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Relation extends Entity
{
    private int $id;

    private int $propertyId;

    private ?string $relationTypeId;

    private ?int $relationTargetId;

    private ?int $selectionRelationId;

    private ?string $createdAt;

    private string $updatedAt;

    /** @var RelationValue[] */
    private array $relationValues = [];

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyId = (int)$data['propertyId'];
        // Unknown type - received only null values
        $this->relationTypeId = is_null($data['relationTypeIdentifier']) ? null : $data['relationTypeIdentifier'];
        // Unknown type - received only null values.
        $this->relationTargetId = is_null($data['relationTargetId']) ? null : $data['relationTargetId'];
        $this->selectionRelationId = is_null($data['selectionRelationId']) ? null : (int)$data['selectionRelationId'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['relationValues'])) {
            foreach ($data['relationValues'] as $relationValue) {
                $this->relationValues[] = new RelationValue($relationValue);
            }
        }
    }

    public function getData(): array
    {
        $relationValues = [];
        foreach ($this->relationValues as $relationValue) {
            $relationValues[] = $relationValue->getData();
        }

        return [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'relationTypeIdentifier' => $this->relationTypeId,
            'relationTargetId' => $this->relationTargetId,
            'selectionRelationId' => $this->selectionRelationId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'relationValues' => $relationValues
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getRelationTypeIdentifier(): ?int
    {
        return $this->relationTypeId;
    }

    public function getRelationTargetId(): ?int
    {
        return $this->relationTargetId;
    }

    public function getSelectionRelationId(): ?int
    {
        return $this->selectionRelationId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @return RelationValue[]
     */
    public function getRelationValues(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->relationValues;
    }
}
