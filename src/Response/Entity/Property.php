<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Option;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Amazon;

class Property extends Entity
{
    /** @var int  */
    private $id;

    /** @var string  */
    private $cast;

    /** @var string  */
    private $typeIdentifier;

    /** @var int  */
    private $position;

    /** @var string  */
    private $createdAt;

    /** @var string  */
    private $updatedAt;

    /** @var string  */
    private $propertyId;

    /** @var string  */
    private $propertyGroupId;

    /** @var Group[] */
    private $groups = [];

    /** @var mixed */
    private $availabilities;

    /** @var Name[] */
    private $names = [];

    /** @var Option[] */
    private $options = [];

    /** @var mixed */
    private $markets;

    /** @var Selection[] */
    private $selections = [];

    /** @var Amazon[] */
    private $amazons = [];

    public function __construct(array $data)
    {
        //The documentation completely differs from what is actually received
        $this->id = (int)$data['id'];
        $this->cast = (string)$data['cast'];
        $this->typeIdentifier = (string)$data['typeIdentifier'];
        $this->position = (int)$data['position'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->propertyId = (string)$data['propertyId'];
        $this->propertyGroupId = (string)$data['propertyGroupId'];
        $this->availabilities = $data['availabilities']; //Unknown structure - undocumented, got only empty arrays.
        $this->markets = $data['markets']; //Unknown structure - undocumented, got only empty arrays.

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

        if (!empty($data['selections'])) {
            foreach ($data['selections'] as $selection) {
                $this->selections[] = new Selection($selection);
            }
        }

        if (!empty($data['amazons'])) {
            foreach ($data['amazons'] as $amazon) {
                $this->amazons[] = new Amazon($amazon);
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

        $selections = [];
        foreach ($this->selections as $selection) {
            $selections[] = $selection->getData();
        }

        $amazons = [];
        foreach ($this->amazons as $amazon) {
            $amazons[] = $amazon->getData();
        }

        return [
            'id' => $this->id,
            'cast' => $this->cast,
            'typeIdentifier' => $this->typeIdentifier,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'propertyId' => $this->propertyId,
            'propertyGroupId' => $this->propertyGroupId,
            'availabilities' => $this->availabilities,
            'markets' => $this->markets,
            'groups' => $groups,
            'names' => $names,
            'options' => $options,
            'selections' => $selections,
            'amazons' => $amazons
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

    public function getTypeIdentifier(): string
    {
        return $this->typeIdentifier;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getPropertyId(): string
    {
        return $this->propertyId;
    }

    public function getPropertyGroupId(): string
    {
        return $this->propertyGroupId;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getAvailabilities()
    {
        return $this->availabilities;
    }

    public function getNames(): array
    {
        return $this->names;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getMarkets()
    {
        return $this->markets;
    }

    public function getSelections(): array
    {
        return $this->selections;
    }

    public function getAmazons(): array
    {
        return $this->amazons;
    }
}
