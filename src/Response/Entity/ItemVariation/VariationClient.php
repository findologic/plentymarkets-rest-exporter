<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationClient extends Entity
{
    private int $variationId;

    private int $plentyId;

    private string $createdAt;

    public function __construct(array $data)
    {
        $this->variationId = (int)$data['variationId'];
        $this->plentyId = (int)$data['plentyId'];
        $this->createdAt = (string)$data['createdAt'];
    }

    public function getData(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return [
            'variationId' => $this->variationId,
            'plentyId' => $this->plentyId,
            'createdAt' => $this->createdAt
        ];
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getPlentyId(): int
    {
        return $this->plentyId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
