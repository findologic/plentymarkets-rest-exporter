<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PropertyOption extends Entity
{
    /** @var int */
    private $id;

    /** @var PropertyOptionValue[] */
    private $values;

    /** @var int */
    private $propertyId;

    /** @var string */
    private $typeOptionIdentifier;

    /** @var DateTimeInterface */
    private $createdAt;

    /** @var DateTimeInterface */
    private $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->values = $this->getEntities(PropertyOptionValue::class, 'propertyOptionValues', $data);
        $this->propertyId = $this->getIntProperty('propertyId', $data);
        $this->typeOptionIdentifier = $this->getStringProperty('typeOptionIdentifier', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyOptionValues' => $this->values,
            'propertyId' => $this->propertyId,
            'typeOptionIdentifier' => $this->typeOptionIdentifier,
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PropertyOptionValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getTypeOptionIdentifier(): string
    {
        return $this->typeOptionIdentifier;
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
