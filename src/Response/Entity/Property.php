<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Amazon;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Option;

class Property extends Entity
{
    private ?int $id;
    private ?string $cast;
    private ?string $type;
    private ?int $position;
    private ?string $createdAt;
    private ?string $updatedAt;
    /** @var Group[] */
    private array $groups;
    /** @var Name[] */
    private array $names;
    /** @var Option[] */
    private array $options;
    /** @var Amazon[] */
    private array $amazon;

    private bool $skipExport = false;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->cast = $this->getStringProperty('cast', $data);
        $this->type = $this->getStringProperty('type', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);
        $this->groups = $this->getEntities(Group::class, 'groups', $data);
        $this->names = $this->getEntities(Name::class, 'names', $data);
        $this->options = $this->getEntities(Option::class, 'options', $data);
        $this->amazon = $this->getEntities(Amazon::class, 'amazon', $data);
    }

    public function getData(): array
    {
        $groups = [];
        foreach ($this->groups as $group) {
            $groups[] = $group->getData();
        }

        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        $options = [];
        foreach ($this->options as $option) {
            $options[] = $option->getData();
        }

        $amazons = [];
        foreach ($this->amazon as $amazon) {
            $amazons[] = $amazon->getData();
        }

        return [
            'id' => $this->id,
            'cast' => $this->cast,
            'type' => $this->type,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'groups' => $groups,
            'names' => $names,
            'options' => $options,
            'amazon' => $amazons
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCast(): ?string
    {
        return $this->cast;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getCreatedAt(): ?string
    {
        // Undocumented
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        // Undocumented
        return $this->updatedAt;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->groups;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->options;
    }

    /**
     * @return Amazon[]
     */
    public function getAmazon(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->amazon;
    }

    public function setSkipExport(bool $skipFlag): void
    {
        $this->skipExport = $skipFlag;
    }

    public function getSkipExport(): bool
    {
        return $this->skipExport;
    }
}
