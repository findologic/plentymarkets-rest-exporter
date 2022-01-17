<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Amazon extends Entity
{
    private int $id;
    private int $propertyId;
    private string $platform;
    private string $category;
    private string $field;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyId = (int)$data['propertyId'];
        $this->platform = (string)$data['platform'];
        $this->category = (string)$data['category'];
        $this->field = (string)$data['field'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'platform' => $this->platform,
            'category' => $this->category,
            'field' => $this->field,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
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

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getField(): string
    {
        return $this->field;
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
