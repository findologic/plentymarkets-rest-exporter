<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $propertyGroupId;

    /** @var string */
    private $lang;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    public function __construct(array $data)
    {
        //Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->propertyGroupId = (int)$data['propertyGroupId'];
        $this->lang = (string)$data['lang'];
        $this->name = (string)$data['name'];
        $this->description = (string)$data['description'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'propertyGroupId' => $this->propertyGroupId,
            'lang' => $this->lang,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPropertyGroupId(): int
    {
        return $this->propertyGroupId;
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

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}