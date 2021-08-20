<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Pivot extends Entity
{
    private ?int $propertyId;

    private ?int $groupId;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->propertyId = $this->getIntProperty('propertyId', $data);
        $this->groupId = $this->getIntProperty('groupId', $data);
    }

    public function getData(): array
    {
        return [
            'propertyId' => $this->propertyId,
            'groupId' => $this->groupId
        ];
    }

    public function getPropertyId(): ?int
    {
        return $this->propertyId;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }
}
