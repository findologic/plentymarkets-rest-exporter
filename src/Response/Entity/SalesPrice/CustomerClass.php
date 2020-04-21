<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class CustomerClass extends Entity
{
    /** @var int */
    private $salesPriceId;

    /** @var int */
    private $customerClassId;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    public function __construct(array $data)
    {
        //Undocumented - the properties may not match the received data exactly
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->customerClassId = (int)$data['customerClassId'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->salesPriceId,
            'customerClassId' => $this->customerClassId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getCustomerClassId(): int
    {
        return $this->customerClassId;
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
