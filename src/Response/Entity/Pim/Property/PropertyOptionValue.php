<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PropertyOptionValue extends Entity
{
    private int $id;
    private ?int $optionId;
    private string $value;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->optionId = $this->getIntProperty('optionId', $data);
        $this->value = $this->getStringProperty('value', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'optionId' => $this->optionId,
            'value' => $this->value,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOptionId(): ?int
    {
        return $this->optionId;
    }

    public function getValue(): string
    {
        return $this->value;
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
