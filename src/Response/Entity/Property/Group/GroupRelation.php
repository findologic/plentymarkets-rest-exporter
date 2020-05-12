<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class GroupRelation extends Entity
{
    /** @var int */
    private $propertyId;

    /** @var int */
    private $propertyGroupId;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->propertyId = (int)$data['propertyId'];
        $this->propertyGroupId = (int)$data['propertyGroupId'];
    }

    public function getData(): array
    {
        return [
            'propertyId' => $this->propertyId,
            'propertyGroupId' => $this->propertyGroupId
        ];
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getPropertyGroupId(): int
    {
        return $this->propertyGroupId;
    }
}
