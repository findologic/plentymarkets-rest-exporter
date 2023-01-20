<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PropertyData extends Entity
{
    private ?int $id;

    /** @var PropertyOption[] */
    private array $options;

    /** @var PropertySelection[] */
    private array $selections;

    /** @var PropertyName[] */
    private array $names;

    private ?int $position;

    private ?string $type;

    private ?string $cast;

    private ?DateTimeInterface $createdAt;

    private ?DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->options = $this->getEntities(PropertyOption::class, 'options', $data);
        $this->selections = $this->getEntities(PropertySelection::class, 'selections', $data);
        $this->names = $this->getEntities(PropertyName::class, 'names', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->type = $this->getStringProperty('type', $data);
        $this->cast = $this->getStringProperty('cast', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'options' => $this->options,
            'selections' => $this->selections,
            'names' => $this->names,
            'position' => $this->position,
            'type' => $this->type,
            'cast' => $this->cast,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PropertyOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return PropertySelection[]
     */
    public function getSelections(): array
    {
        return $this->selections;
    }

    /**
     * @return PropertyName[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCast(): string
    {
        return $this->cast;
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
