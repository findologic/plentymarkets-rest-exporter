<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Amazon;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Option;

class Property extends Entity
{
    /** @var int  */
    private $id;

    /** @var string  */
    private $cast;

    /** @var string  */
    private $type;

    /** @var int  */
    private $position;

    /** @var string  */
    private $createdAt;

    /** @var string  */
    private $updatedAt;

    /** @var Group[] */
    private $groups = [];

    /** @var Name[] */
    private $names = [];

    /** @var Option[] */
    private $options = [];

    /** @var Amazon[] */
    private $amazon = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->cast = (string)$data['cast'];
        $this->type = (string)$data['type'];
        $this->position = (int)$data['position'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $this->groups[] = new Group($group);
            }
        }

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }

        if (!empty($data['options'])) {
            foreach ($data['options'] as $option) {
                $this->options[] = new Option($option);
            }
        }

        if (!empty($data['amazon'])) {
            foreach ($data['amazon'] as $amazon) {
                $this->amazon[] = new Amazon($amazon);
            }
        }
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getCast(): string
    {
        return $this->cast;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getCreatedAt(): string
    {
        // Undocumented
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
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
}
