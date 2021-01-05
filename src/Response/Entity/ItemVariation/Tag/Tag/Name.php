<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag\Tag;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Name extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $tagId;

    /** @var string */
    private $tagLang;

    /** @var string */
    private $tagName;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->tagId = (int)$data['tagId'];
        $this->tagLang = (string)$data['tagLang'];
        $this->tagName = (string)$data['tagName'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'tagId' => $this->tagId,
            'tagLang' => $this->tagLang,
            'tagName' => $this->tagName,
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

    public function getTagLang(): string
    {
        return $this->tagLang;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }
}
