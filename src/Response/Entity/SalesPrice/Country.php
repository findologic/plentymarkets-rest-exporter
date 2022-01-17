<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Country extends Entity
{
    private int $salesPriceId;
    private int $countryId;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->countryId = (int)$data['countryId'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->salesPriceId,
            'countryId' => $this->countryId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getCountryId(): int
    {
        return $this->countryId;
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
