<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class TagData extends Entity
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $color;

    /** @var TagName[] */
    private $names;

    /** @var TagClient[] */
    private $clients;

    /** @var DateTimeInterface */
    private $createdAt;

    /** @var DateTimeInterface */
    private $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->name = $this->getStringProperty('tagName', $data);
        $this->color = $this->getStringProperty('color', $data);
        $this->names = $this->getEntities(TagName::class, 'names', $data);
        $this->clients = $this->getEntities(TagClient::class, 'clients', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'names' => $this->names,
            'clients' => $this->clients,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
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

    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return TagName[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @return TagClient[]
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
