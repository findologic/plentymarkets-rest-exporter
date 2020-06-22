<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationSalesPrice extends Entity
{
    /** @var int */
    private $variationId;

    /** @var int */
    private $salesPriceId;

    /** @var float */
    private $price;

    /** @var string */
    private $updatedAt;

    /** @var string */
    private $createdAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->variationId = (int)$data['variationId'];
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->price = (float)$data['price'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->createdAt = (string)$data['createdAt'];
    }

    public function getData(): array
    {
        return [
            'variationId' => $this->variationId,
            'salesPriceId' => $this->salesPriceId,
            'price' => $this->price,
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt
        ];
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
