<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    /** @var int */
    private $propertyValueId;

    /** @var string */
    private $lang;

    /** @var string */
    private $value;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->propertyValueId = (int)$data['propertyValueId'];
        $this->lang = (string)$data['lang'];
        $this->value = (string)$data['value'];
    }

    public function getData(): array
    {
        return [
            'propertyValueId' => $this->propertyValueId,
            'lang' => $this->lang,
            'value' => $this->value
        ];
    }

    public function getPropertyValueId(): int
    {
        return $this->propertyValueId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
