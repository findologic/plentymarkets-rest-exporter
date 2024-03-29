<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class AttributeValue extends Entity
{
    private ?int $id;

    private ?int $attributeId;

    private ?int $position;

    private ?string $image;

    private ?string $comment;

    /** @var AttributeValueName[] */
    private array $names;

    private ?string $backendName;

    private ?string $amazonValue;

    private ?string $ottoValue;

    private ?string $neckermannAtEpValue;

    private ?string $laRedouteValue;

    private ?string $tracdelightValue;

    private ?float $percentageDistribution;

    private ?DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->attributeId = $this->getIntProperty('attributeId', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->image = $this->getStringProperty('image', $data);
        $this->comment = $this->getStringProperty('comment', $data);
        $this->names = $this->getEntities(AttributeValueName::class, 'valueNames', $data);
        $this->backendName = $this->getStringProperty('backendName', $data);
        $this->amazonValue = $this->getStringProperty('amazonValue', $data);
        $this->ottoValue = $this->getStringProperty('ottoValue', $data);
        $this->neckermannAtEpValue = $this->getStringProperty('neckermannAtEpValue', $data);
        $this->laRedouteValue = $this->getStringProperty('laRedouteValue', $data);
        $this->tracdelightValue = $this->getStringProperty('tracdelightValue', $data);
        $this->percentageDistribution = $this->getFloatProperty('percentageDistribution', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'attributeId' => $this->attributeId,
            'position' => $this->position,
            'image' => $this->image,
            'comment' => $this->comment,
            'valueNames' => $this->names,
            'backendName' => $this->backendName,
            'amazonValue' => $this->amazonValue,
            'ottoValue' => $this->ottoValue,
            'neckermannAtEpValue' => $this->neckermannAtEpValue,
            'laRedouteValue' => $this->laRedouteValue,
            'tracdelightValue' => $this->tracdelightValue,
            'percentageDistribution' => $this->percentageDistribution,
            'updatedAt' => $this->updatedAt,
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

    /**
     * @return AttributeValueName[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    public function getBackendName(): string
    {
        return $this->backendName;
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

    public function getPercentageDistribution(): float
    {
        return $this->percentageDistribution;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
