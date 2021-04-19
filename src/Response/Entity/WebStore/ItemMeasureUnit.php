<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ItemMeasureUnit extends Entity
{
    private string $name;
    private ?array $options;

    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->options = $this->getArrayProperty('options', $data);
    }

    public function getData(): array
    {
        return [
            $this->name => [
                'options' => $this->options
            ]
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }
}
