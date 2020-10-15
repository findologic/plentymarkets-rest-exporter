<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Tag extends Entity
{
    /** @var int */
    private $id;

    /** @var TagData */
    private $tagData;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('tagId', $data);
        $this->tagData = $this->getEntity(TagData::class, $data['tag']);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'tagData' => $this->tagData,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTagData(): TagData
    {
        return $this->tagData;
    }
}