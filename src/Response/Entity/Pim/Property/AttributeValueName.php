<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class AttributeValueName extends Entity
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $lang;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('valueId', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->lang = $this->getStringProperty('lang', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lang' => $this->lang,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLang(): string
    {
        return $this->lang;
    }
}
