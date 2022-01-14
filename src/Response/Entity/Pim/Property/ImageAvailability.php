<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ImageAvailability extends Entity
{
    private int $id;
    private int $value;
    private string $type;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('imageId', $data);
        $this->value = $this->getIntProperty('value', $data);
        $this->type = $this->getStringProperty('type', $data);
    }

    public function getData(): array
    {
        return [
            'imageId' => $this->id,
            'value' => $this->value,
            'type' => $this->type,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
