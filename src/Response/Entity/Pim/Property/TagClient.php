<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class TagClient extends Entity
{
    private ?int $id;
    private ?int $tagId;
    private ?int $plentyId;
    private ?DateTimeInterface $createdAt;
    private ?DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->tagId = $this->getIntProperty('tagId', $data);
        $this->plentyId = $this->getIntProperty('plentyId', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'tagId' => $this->tagId,
            'plentyId' => $this->plentyId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function getPlentyId(): int
    {
        return $this->plentyId;
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
