<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group\GroupRelation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Group extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $position;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /** @var Name[] */
    private $names = [];

    /** @var GroupRelation */
    private $groupRelation;

    public function __construct(array $data)
    {
        //Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->position = (int)$data['position'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }

        if (!empty($data['groupRelation'])) {
            $this->groupRelation = new GroupRelation($data['groupRelation']);
        }
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        $data =  [
            'id' => $this->id,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'names' => $names
        ];

        if ($this->groupRelation) {
            $data['groupRelation'] = $this->groupRelation->getData();
        }

        return $data;
    }

    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    public function getGroupRelation(): ?GroupRelation
    {
        return $this->groupRelation;
    }
}