<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class ItemProperty extends Entity
{
    private int $id;

    private int $position;

    private ?int $propertyGroupId;

    private string $unit;

    private string $backendName;

    private string $comment;

    private string $valueType;

    private bool $isSearchable;

    private bool $isOderProperty;

    private bool $isShownOnItemPage;

    private bool $isShownOnItemList;

    private bool $isShownAtCheckout;

    private bool $isShownInPdf;

    private bool $isShownAsAdditionalCosts;

    private float $surcharge;

    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->position = (int)$data['position'];
        $this->propertyGroupId = !is_null($data['propertyGroupId']) ? (int)$data['propertyGroupId'] : null;
        $this->unit = (string)$data['unit'];
        $this->backendName = (string)$data['backendName'];
        $this->comment = (string)$data['comment'];
        $this->valueType = (string)$data['valueType'];
        $this->isSearchable = (bool)$data['isSearchable'];
        $this->isOderProperty = (bool)$data['isOderProperty'];
        $this->isShownOnItemPage = (bool)$data['isShownOnItemPage'];
        $this->isShownOnItemList = (bool)$data['isShownOnItemList'];
        $this->isShownAtCheckout = (bool)$data['isShownAtCheckout'];
        $this->isShownInPdf = (bool)$data['isShownInPdf'];
        $this->isShownAsAdditionalCosts = (bool)$data['isShownAsAdditionalCosts'];
        $this->surcharge = (float)$data['surcharge'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'unit' => $this->unit,
            'propertyGroupId' => $this->propertyGroupId,
            'backendName' => $this->backendName,
            'valueType' => $this->valueType,
            'isSearchable' => $this->isSearchable,
            'isOderProperty' => $this->isOderProperty,
            'isShownOnItemPage' => $this->isShownOnItemPage,
            'isShownOnItemList' => $this->isShownOnItemList,
            'isShownAtCheckout' => $this->isShownAtCheckout,
            'isShownInPdf' => $this->isShownInPdf,
            'comment' => $this->comment,
            'surcharge' => $this->surcharge,
            'isShownAsAdditionalCosts' => $this->isShownAsAdditionalCosts,
            'updatedAt' => $this->updatedAt
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

    public function getPropertyGroupId(): ?int
    {
        return $this->propertyGroupId;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getBackendName(): string
    {
        return $this->backendName;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function isOderProperty(): bool
    {
        return $this->isOderProperty;
    }

    public function isShownOnItemPage(): bool
    {
        return $this->isShownOnItemPage;
    }

    public function isShownOnItemList(): bool
    {
        return $this->isShownOnItemList;
    }

    public function isShownAtCheckout(): bool
    {
        return $this->isShownAtCheckout;
    }

    public function isShownInPdf(): bool
    {
        return $this->isShownInPdf;
    }

    public function isShownAsAdditionalCosts(): bool
    {
        return $this->isShownAsAdditionalCosts;
    }

    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
