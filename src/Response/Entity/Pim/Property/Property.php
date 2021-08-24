<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Property extends Entity
{
    private ?int $id;

    private array $values = [];

    private ?PropertyData $propertyData;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('propertyId', $data);
        $this->values = $this->getEntities(PropertyValue::class, 'values', $data);

        if (isset($data['property'])) {
            /** @var PropertyData $propertyData */
            $propertyData = $this->getEntity(PropertyData::class, $data['property']);
            $this->propertyData = $propertyData;
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

    public function getId(): ?int
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
