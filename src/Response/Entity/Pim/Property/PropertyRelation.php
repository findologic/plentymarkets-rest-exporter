<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PropertyRelation extends Entity
{
    private ?int $id;
    
    private ?int $propertyId;

    private ?int $relationTargetId;

    /** @var PropertyRelationValue[] */
    private array $values;

    private ?int $relationTypeIdentifier;

    private ?int $selectionRelationId;

    private ?DateTimeInterface $createdAt;

    private ?DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->propertyId = $this->getIntProperty('propertyId', $data);
        $this->relationTargetId = $this->getIntProperty('relationTargetId', $data);
        $this->values = $this->getEntities(PropertyRelationValue::class, 'relationValues', $data);
        $this->relationTypeIdentifier = $this->getIntProperty('relationTypeIdentifier', $data);
        $this->selectionRelationId = $this->getIntProperty('selectionRelationId', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'relationTargetId' => $this->relationTargetId,
            'relationValues' => $this->values,
            'relationTypeIdentifier' => $this->relationTypeIdentifier,
            'selectionRelationId' => $this->selectionRelationId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
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

    public function getRelationTargetId(): ?int
    {
        return $this->relationTargetId;
    }

    /**
     * @return PropertyRelationValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getRelationTypeIdentifier(): ?int
    {
        return $this->relationTypeIdentifier;
    }

    public function getSelectionRelationId(): int
    {
        return $this->selectionRelationId;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
