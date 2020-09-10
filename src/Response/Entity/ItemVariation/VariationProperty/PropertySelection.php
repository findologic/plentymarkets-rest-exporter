<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PropertySelection extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $propertyId;

    /** @var string */
    private $lang;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyId = (int)$data['propertyId'];
        $this->lang = (string)$data['lang'];
        $this->name = (string)$data['name'];
        $this->description = (string)$data['description'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyId' => $this->propertyId,
            'lang' => $this->lang,
            'name' => $this->name,
            'description' => $this->description
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
