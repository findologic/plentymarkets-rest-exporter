<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group\Pivot;

class Group extends Entity
{
    private ?int $id;
    private ?int $position;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?Pivot $pivot = null;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = $this->getIntProperty('id', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);

        if (!empty($data['pivot'])) {
            /** @var Pivot $pivot */
            $pivot = $this->getEntity(Pivot::class, $data['pivot']);
            $this->pivot = $pivot;
        }
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'pivot' => $this->pivot ? $this->pivot->getData() : []
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getPivot(): ?Pivot
    {
        // Undocumented
        return $this->pivot;
    }
}
