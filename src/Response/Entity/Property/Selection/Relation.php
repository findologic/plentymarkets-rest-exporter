<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation\RelationValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Relation extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $propertyId;

    /** @var string|null */
    private $relationTypeId;

    /** @var int|null */
    private $relationTargetId;

    /** @var int|null */
    private $selectionRelationId;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /** @var RelationValue[] */
    private $relationValues = [];

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyId = (int)$data['propertyId'];
        $this->relationTypeId = $this->getStringProperty('relationTypeIdentifier', $data);
        $this->relationTargetId = $this->getIntProperty('relationTargetId', $data);
        $this->selectionRelationId = $this->getIntProperty('selectionRelationId', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);

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

    public function getRelationTypeIdentifier(): ?string
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
