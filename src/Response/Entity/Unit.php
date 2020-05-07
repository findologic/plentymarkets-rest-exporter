<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit\Name;

class Unit extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $position;

    /** @var string */
    private $unitOfMeasurement;

    /** @var bool */
    private $isDecimalPlacesAllowed;

    /** @var string */
    private $updatedAt;

    /** @var string */
    private $createdAt;

    /** @var Name[] */
    private $names = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->position = (int)$data['position'];
        $this->unitOfMeasurement = (string)$data['unitOfMeasurement'];
        $this->isDecimalPlacesAllowed = (bool)$data['isDecimalPlacesAllowed'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->createdAt = (string)$data['createdAt'];

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        return [
            'id' => $this->id,
            'position' => $this->position,
            'unitOfMeasurement' => $this->unitOfMeasurement,
            'isDecimalPlacesAllowed' => $this->isDecimalPlacesAllowed,
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt,
            'names' => $names
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getUnitOfMeasurement(): string
    {
        return $this->unitOfMeasurement;
    }

    public function isDecimalPlacesAllowed(): bool
    {
        return $this->isDecimalPlacesAllowed;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }
}
