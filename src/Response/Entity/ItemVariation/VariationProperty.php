<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty\PropertySelection;

class VariationProperty extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $itemId;

    /** @var int */
    private $propertyId;

    /** @var int|null */
    private $propertySelectionId;

    /** @var int|null */
    private $valueInt;

    /** @var float|null */
    private $valueFloat;

    private $valueFile;

    /** @var float */
    private $surcharge;

    /** @var string */
    private $updatedAt;

    /** @var string */
    private $createdAt;

    /** @var int */
    private $variationId;

    /** @var Name[] */
    private $names = [];

    /** @var PropertySelection[] */
    private $propertySelection = [];

    /** @var ItemProperty|null */
    private $property;

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

        // Undocumented - the properties may not match the received data exactly
        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }
        // Undocumented - the properties may not match the received data exactly
        if (!empty($data['propertySelection'])) {
            foreach ($data['propertySelection'] as $propertySelection) {
                $this->propertySelection[] = new PropertySelection($propertySelection);
            }
        }

        if (!empty($data['property'])) {
            $this->property = new ItemProperty($data['property']);
        }
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        $propertySelections = [];
        foreach ($this->propertySelection as $propertySelection) {
            $propertySelections[] = $propertySelection->getData();
        }

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
            'names' => $names,
            'propertySelection' => $propertySelections,
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

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }

    /**
     * @return PropertySelection[]
     */
    public function getPropertySelection(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->propertySelection;
    }

    public function getProperty(): ?ItemProperty
    {
        return $this->property;
    }

    public function getPropertyName(string $lang): ?string
    {
        $value = $this->getProperty()->getBackendName();

        foreach ($this->getNames() as $name) {
            if (strtoupper($name->getLang()) == strtoupper($lang)) {
                return $name->getValue();
            }
        }

        return $value;
    }
}
