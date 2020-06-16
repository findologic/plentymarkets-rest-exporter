<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Property as SelectionProperty;

class Property extends Relation
{
    private ?SelectionProperty $propertyRelation;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        parent::__construct($data);

        if (!empty($data['propertyRelation'])) {
            $this->propertyRelation = new SelectionProperty($data['propertyRelation']);
        }
    }

    public function getData(): array
    {
        $data = parent::getData();

        if ($this->propertyRelation) {
            $data['propertyRelation'] = $this->propertyRelation->getData();
        }

        return $data;
    }

    public function getPropertyRelation(): ?SelectionProperty
    {
        return $this->propertyRelation;
    }
}
