<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationCategory extends Entity
{
    private int $variationId;

    private int $categoryId;

    private int $position;

    private bool $isNeckermannPrimary;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->variationId = (int)$data['variationId'];
        $this->categoryId = (int)$data['categoryId'];
        $this->position = (int)$data['position'];
        $this->isNeckermannPrimary = (bool)$data['isNeckermannPrimary'];
    }

    public function getData(): array
    {
        return [
            'variationId' => $this->variationId,
            'categoryId' => $this->categoryId,
            'position' => $this->position,
            'isNeckermannPrimary' => $this->isNeckermannPrimary
        ];
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isNeckermannPrimary(): bool
    {
        return $this->isNeckermannPrimary;
    }
}
