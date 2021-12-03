<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ImageAttributeValue extends Entity
{
    /** @var int */
    private $itemId;

    /** @var int */
    private $valueId;

    /** @var int */
    private $imageId;

    /** @var int */
    private $attributeId;

    public function __construct(array $data)
    {
        $this->itemId = $this->getIntProperty('itemId', $data);
        $this->valueId = $this->getIntProperty('valueId', $data);
        $this->imageId = $this->getIntProperty('imageId', $data);
        $this->attributeId = $this->getIntProperty('attributeId', $data);
    }

    public function getData(): array
    {
        return [
            'itemId' => $this->itemId,
            'valueId' => $this->valueId,
            'imageId' => $this->imageId,
            'attributeId' => $this->attributeId,
        ];
    }

    public function getImageId(): int
    {
        return $this->imageId;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }
}
