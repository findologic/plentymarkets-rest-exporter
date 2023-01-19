<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationTag extends Entity
{
    private string $tagId;

    private string $tagType;

    private string $relationshipValue;

    private string $relationshipUUID5;

    private string $createdAt;

    private string $updatedAt;

    private ?Tag $tag;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->tagId = (string)$data['tagId'];
        $this->tagType = (string)$data['tagType'];
        $this->relationshipValue = (string)$data['relationshipValue'];
        $this->relationshipUUID5 = (string)$data['relationshipUUID5'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['tag'])) {
            $this->tag = new Tag($data['tag']);
        }
    }

    public function getData(): array
    {
        $data =  [
            'tagId' => $this->tagId,
            'tagType' => $this->tagType,
            'relationshipValue' => $this->relationshipValue,
            'relationshipUUID5' => $this->relationshipUUID5,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];

        if ($this->tag) {
            $data['tag'] = $this->tag->getData();
        }

        return $data;
    }

    public function getTagId(): string
    {
        return $this->tagId;
    }

    public function getTagType(): string
    {
        return $this->tagType;
    }

    public function getRelationshipValue(): string
    {
        return $this->relationshipValue;
    }

    public function getRelationshipUUID5(): string
    {
        return $this->relationshipUUID5;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getTag(): ?Tag
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->tag;
    }
}
