<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Property extends Entity
{
    /** @var int */
    private $id;

    /** @var PropertyValue[] */
    private $values;

    /** @var PropertyData|null */
    private $propertyData;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('propertyId', $data);
        $this->values = $this->getEntities(PropertyValue::class, 'values', $data);

        if (isset($data['property'])) {
            $this->propertyData = $this->getEntity(PropertyData::class, $data['property']);
        }
    }

    public function getData(): array
    {
        return [
            'propertyId' => $this->id,
            'values' => $this->values,
            'property' => $this->propertyData,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PropertyValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getPropertyData(): ?PropertyData
    {
        return $this->propertyData;
    }
}
