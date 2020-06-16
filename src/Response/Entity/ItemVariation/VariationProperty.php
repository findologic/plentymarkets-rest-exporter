<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;

class VariationProperty extends Entity
{
    private int $id;

    private int $itemId;

    private int $propertyId;

    private ?int $propertySelectionId;

    private ?int $valueInt;

    private ?float $valueFloat;

    private ?string $valueFile;

    private float $surcharge;

    private string $updatedAt;

    private string $createdAt;

    private int $variationId;

    private array $names = [];

    private array $propertySelection = [];

    private ?ItemProperty $property;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->itemId = (int)$data['itemId'];
        $this->propertyId = (int)$data['propertyId'];
        $this->propertySelectionId = is_null($data['propertySelectionId']) ? null : (int)$data['propertySelectionId'];
        $this->valueInt = is_null($data['valueInt']) ? null : (int)$data['valueInt'];
        // Never received anything other than null
        $this->valueFloat = is_null($data['valueFloat']) ? null : (float)$data['valueFloat'];
        $this->valueFile = $data['valueFile']; // Unknown type
        $this->surcharge = (float)$data['surcharge'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->createdAt = (string)$data['createdAt'];
        $this->variationId = (int)$data['variationId'];
        $this->names = $data['names']; // Unknown structure - received only empty arrays
        $this->propertySelection = $data['propertySelection']; // Unknown structure - received only empty arrays

        if (!empty($data['property'])) {
            $this->property = new ItemProperty($data['property']);
        }
    }

    public function getData(): array
    {
        $data = [
            'id' => $this->id,
            'itemId' => $this->itemId,
            'propertyId' => $this->propertyId,
            'propertySelectionId' => $this->propertySelectionId,
            'valueInt' => $this->valueInt,
            'valueFloat' => $this->valueFloat,
            'valueFile' => $this->valueFile,
            'surcharge' => $this->surcharge,
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt,
            'variationId' => $this->variationId,
            'names' => $this->names,
            'propertySelection' => $this->propertySelection
        ];

        if ($this->property) {
            $data['property'] = $this->property->getData();
        }

        return $data;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getPropertySelectionId(): ?int
    {
        return $this->propertySelectionId;
    }

    public function getValueInt(): ?int
    {
        return $this->valueInt;
    }

    public function getValueFloat(): ?float
    {
        return $this->valueFloat;
    }

    public function getValueFile()
    {
        return $this->valueFile;
    }

    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getNames()
    {
        return $this->names;
    }

    public function getPropertySelection()
    {
        return $this->propertySelection;
    }

    public function getProperty(): ?ItemProperty
    {
        return $this->property;
    }
}
