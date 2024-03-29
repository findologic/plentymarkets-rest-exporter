<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag\Tag\Name;

class Tag extends Entity
{
    private int $id;

    private string $tagName;

    private ?string $color;

    private string $createdAt;

    private string $updatedAt;

    /** @var Name[] */
    private array $names = [];

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->tagName = (string)$data['tagName'];
        $this->color = is_null($data['color']) ? null : (string)$data['color'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] =  $name->getData();
        }

        return [
            'id' => $this->id,
            'tagName' => $this->tagName,
            'color' => $this->color,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'names' => $names
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getColor(): ?string
    {
        return $this->color;
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
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }
}
