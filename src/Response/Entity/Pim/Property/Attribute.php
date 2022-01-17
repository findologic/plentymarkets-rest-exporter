<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Attribute extends Entity
{
    private ?int $id;
    private ?int $valueSetId;
    private ?int $valueId;
    private ?AttributeValue $value;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('attributeId', $data);
        $this->valueSetId = $this->getIntProperty('attributeValueSetId', $data);
        $this->valueId = $this->getIntProperty('valueId', $data);

        if (isset($data['attributeValue'])) {
            $this->value = $this->getEntity(AttributeValue::class, $data['attributeValue']);
        }
    }

    public function getData(): array
    {
        return [
            'attributeId' => $this->id,
            'attributeValueSetId' => $this->valueSetId,
            'valueId' => $this->valueId,
            'attributeValue' => $this->value
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValueSetId(): int
    {
        return $this->valueSetId;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }

    public function getValue(): AttributeValue
    {
        return $this->value;
    }
}
