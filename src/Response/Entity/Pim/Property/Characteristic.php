<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Characteristic extends Entity
{
    private int $id;
    private int $propertyId;
    private ?int $propertySelectionId;
    private int $itemId;
    private int $variationId;
    private int $surcharge;
    /** @var CharacteristicText[] */
    private array $valueTexts;
    private ?float $valueFloat;
    private ?int $valueInt;
    private ?string $valueFile;
    /** @var CharacteristicSelection[] */
    private array $propertySelections;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->propertyId = $this->getIntProperty('propertyId', $data);
        $this->propertySelectionId = $this->getIntProperty('propertySelectionId', $data);
        $this->itemId = $this->getIntProperty('itemId', $data);
        $this->variationId = $this->getIntProperty('variationId', $data);
        $this->surcharge = $this->getIntProperty('surcharge', $data);
        $this->valueTexts = $this->getEntities(CharacteristicText::class, 'valueTexts', $data);
        $this->valueFloat = $this->getFloatProperty('valueFloat', $data);
        $this->valueInt = $this->getIntProperty('valueInt', $data);
        $this->valueFile = $this->getStringProperty('valueFile', $data);
        $this->propertySelections = $this->getEntities(CharacteristicSelection::class, 'propertySelection', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'propertySelectionId' => $this->propertySelectionId,
            'itemId' => $this->itemId,
            'variationId' => $this->variationId,
            'surcharge' => $this->surcharge,
            'valueTexts' => $this->valueTexts,
            'valueFloat' => $this->valueFloat,
            'valueInt' => $this->valueInt,
            'valueFile' => $this->valueFile,
            'propertySelection' => $this->propertySelections,
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

    public function getPropertySelectionId(): ?int
    {
        return $this->propertySelectionId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getSurcharge(): int
    {
        return $this->surcharge;
    }

    /**
     * @return CharacteristicText[]
     */
    public function getValueTexts(): array
    {
        return $this->valueTexts;
    }

    public function getValueFloat(): ?float
    {
        return $this->valueFloat;
    }

    public function getValueInt(): ?int
    {
        return $this->valueInt;
    }

    public function getValueFile(): ?string
    {
        return $this->valueFile;
    }

    /**
     * @return CharacteristicSelection[]
     */
    public function getPropertySelections(): array
    {
        return $this->propertySelections;
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
