<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Translatable;

class Name extends Entity implements Translatable
{
    private ?int $propertyGroupId;
    private ?string $lang;
    private ?string $name;
    private ?string $description;

    public function __construct(array $data)
    {
        $this->propertyGroupId = $this->getIntProperty('propertyGroupId', $data);
        $this->lang = $this->getStringProperty('lang', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->description = $this->getStringProperty('description', $data);
    }

    public function getData(): array
    {
        return [
            'propertyGroupId' => $this->propertyGroupId,
            'lang' => $this->lang,
            'name' => $this->name,
            'description' => $this->description
        ];
    }

    public function getPropertyGroupId(): ?int
    {
        return $this->propertyGroupId;
    }

    public function getLang(): string
    {
        return $this->lang ?? '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
