<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationAttributeValue;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class AttributeValue extends Entity
{
    private int $id;
    private int $attributeId;
    private string $backendName;
    private int $position;
    private string $image;
    private string $comment;
    private string $amazonValue;
    private string $ottoValue;
    private string $neckermannAtEpValue;
    private string $laRedouteValue;
    private string $tracdelightValue;
    private int $percentageDistribution;
    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->attributeId = (int)$data['attributeId'];
        $this->backendName = (string)$data['backendName'];
        $this->position = (int)$data['position'];
        $this->image = (string)$data['image'];
        $this->comment = (string)$data['comment'];
        $this->amazonValue = (string)$data['amazonValue'];
        $this->ottoValue = (string)$data['ottoValue'];
        $this->neckermannAtEpValue = (string)$data['neckermannAtEpValue'];
        $this->laRedouteValue = (string)$data['laRedouteValue'];
        $this->tracdelightValue = (string)$data['tracdelightValue'];
        $this->percentageDistribution = (int)$data['percentageDistribution'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'attributeId' => $this->attributeId,
            'backendName' => $this->backendName,
            'position' => $this->position,
            'image' => $this->image,
            'comment' => $this->comment,
            'amazonValue' => $this->amazonValue,
            'ottoValue' => $this->ottoValue,
            'neckermannAtEpValue' => $this->neckermannAtEpValue,
            'laRedouteValue' => $this->laRedouteValue,
            'tracdelightValue' => $this->tracdelightValue,
            'percentageDistribution' => $this->percentageDistribution,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    public function getBackendName(): string
    {
        return $this->backendName;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getAmazonValue(): string
    {
        return $this->amazonValue;
    }

    public function getOttoValue(): string
    {
        return $this->ottoValue;
    }

    public function getNeckermannAtEpValue(): string
    {
        return $this->neckermannAtEpValue;
    }

    public function getLaRedouteValue(): string
    {
        return $this->laRedouteValue;
    }

    public function getTracdelightValue(): string
    {
        return $this->tracdelightValue;
    }

    public function getPercentageDistribution(): int
    {
        return $this->percentageDistribution;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
