<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationAttributeValue\AttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationAttributeValue extends Entity
{
    private int $attributeValueSetId;
    private int $attributeId;
    private int $valueId;
    private bool $isLinkableToImage;
    private ?Attribute $attribute;
    private ?AttributeValue $attributeValue;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->attributeValueSetId = (int)$data['attributeValueSetId'];
        $this->attributeId = (int)$data['attributeId'];
        $this->valueId = (int)$data['valueId'];
        $this->isLinkableToImage = (bool)$data['isLinkableToImage'];

        if (!empty($data['attribute'])) {
            $this->attribute = new Attribute($data['attribute']);
        }

        if (!empty($data['attributeValue'])) {
            $this->attributeValue = new AttributeValue($data['attributeValue']);
        }
    }

    public function getData(): array
    {
        $data = [
            'attributeValueSetId' => $this->attributeValueSetId,
            'attributeId' => $this->attributeId,
            'valueId' => $this->valueId,
            'isLinkableToImage' => $this->isLinkableToImage
        ];

        if ($this->attribute) {
            $data['attribute'] = $this->attribute->getData();
        }

        if ($this->attributeValue) {
            $data['attributeValue'] = $this->attributeValue->getData();
        }

        return $data;
    }

    public function getAttributeValueSetId(): int
    {
        return $this->attributeValueSetId;
    }

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }

    public function isLinkableToImage(): bool
    {
        return $this->isLinkableToImage;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function getAttributeValue(): ?AttributeValue
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->attributeValue;
    }
}
