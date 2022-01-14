<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class SalesPrice extends Entity
{
    private int $id;
    private float $price;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('salesPriceId', $data);
        $this->price = $this->getFloatProperty('price', $data);
    }

    public function getData(): array
    {
        return [
            'salesPriceId' => $this->id,
            'price' => $this->price,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
