<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Category extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $position;

    /** @var bool */
    private $isNeckermannPrimary;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('categoryId', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->isNeckermannPrimary = $this->getBoolProperty('isNeckermannPrimary', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'isNeckermannPrimary' => $this->isNeckermannPrimary,
        ];
    }

    public function getId(): int
    {
        return $this->id;
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
