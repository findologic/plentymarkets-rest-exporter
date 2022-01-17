<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ImageName extends Entity
{
    private ?int $id;
    private ?string $lang;
    private ?string $name;
    private ?string $alternate;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('imageId', $data);
        $this->lang = $this->getStringProperty('lang', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->alternate = $this->getStringProperty('alternate', $data);
    }

    public function getData(): array
    {
        return [
            'imageId' => $this->id,
            'lang' => $this->lang,
            'name' => $this->name,
            'alternate' => $this->alternate,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlternate(): string
    {
        return $this->alternate;
    }
}
