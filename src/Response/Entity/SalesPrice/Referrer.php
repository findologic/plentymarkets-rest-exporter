<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Referrer extends Entity
{
    private int $salesPriceId;

    private int $referrerId;

    private string $createdAt;

    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->referrerId = (int)$data['referrerId'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->salesPriceId,
            'referrerId' => $this->referrerId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getReferrerId(): int
    {
        return $this->referrerId;
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
