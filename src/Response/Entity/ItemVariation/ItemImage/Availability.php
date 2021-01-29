<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Availability extends Entity
{
    public const STORE = 'mandant';

    /** @var int */
    private $imageId;

    /** @var string */
    private $type;

    /** @var string */
    private $value;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->imageId = (int)$data['imageId'];
        $this->type = (string)$data['type'];
        $this->value = (string)$data['value'];
    }

    public function getData(): array
    {
        return [
            'imageId' => $this->imageId,
            'type' => $this->type,
            'value' => $this->value
        ];
    }

    public function getImageId(): int
    {
        return $this->imageId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
