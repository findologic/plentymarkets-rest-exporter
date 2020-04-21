<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    /** @var int */
    private $salesPriceId;

    /** @var string */
    private $lang;

    /** @var string */
    private $nameInternal;

    /** @var string */
    private $nameExternal;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    public function __construct(array $data)
    {
        //Undocumented - the properties may not match the received data exactly
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
