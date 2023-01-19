<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VatRate extends Entity
{
    private int $id;

    private string $name;

    private float $vatRate;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->name = (string) $data['name'];
        $this->vatRate = (float)$data['vatRate'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vatRate' => $this->vatRate
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVatRate(): float
    {
        return $this->vatRate;
    }
}
