<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    private int $salesPriceId;

    private string $lang;

    private string $nameInternal;

    private string $nameExternal;

    private string $createdAt;

    private string $updatedAt;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->salesPriceId = (int)$data['salesPriceId'];
        $this->lang = (string)$data['lang'];
        $this->nameInternal = (string)$data['nameInternal'];
        $this->nameExternal = (string)$data['nameExternal'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->salesPriceId,
            'lang' => $this->lang,
            'nameInternal' => $this->nameInternal,
            'nameExternal' => $this->nameExternal,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getSalesPriceId(): int
    {
        return $this->salesPriceId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getNameInternal(): string
    {
        return $this->nameInternal;
    }

    public function getNameExternal(): string
    {
        return $this->nameExternal;
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
