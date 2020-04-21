<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Currency extends Entity
{
    /** @var int */
    private $salesPriceId;

    /** @var string */
    private $currency;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    public function __construct(array $data)
    {
        //Undocumented - the properties may not match the received data exactly
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->currency = (string)$data['currency'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->salesPriceId,
            'currency' => $this->currency,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
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
