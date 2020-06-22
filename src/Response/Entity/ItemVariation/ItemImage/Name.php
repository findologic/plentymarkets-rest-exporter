<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    /** @var int */
    private $imageId;

    /** @var string */
    private $lang;

    /** @var string */
    private $name;

    /** @var string */
    private $alternate;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->imageId = (int)$data['imageId'];
        $this->lang = (string)$data['lang'];
        $this->name = (string)$data['name'];
        $this->alternate = (string)$data['alternate'];
    }

    public function getData(): array
    {
        return [
            'imageId' => $this->imageId,
            'lang' => $this->lang,
            'name' => $this->name,
            'alternate' => $this->alternate
        ];
    }

    public function getImageId(): int
    {
        return $this->imageId;
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
