<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Translatable;

class RelationValue extends Entity implements Translatable
{
    private int $id;

    private int $propertyRelationId;

    private string $lang;

    private string $value;

    private ?string $description;

    private string $createdAt;

    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyRelationId = (int)$data['propertyRelationId'];
        $this->lang = (string)$data['lang'];
        $this->value = (string)$data['value'];
        $this->description = is_null($data['description']) ? null : (string)$data['description'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyRelationId' => $this->propertyRelationId,
            'lang' => $this->lang,
            'value' => $this->value,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPropertyRelationId(): int
    {
        return $this->propertyRelationId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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
