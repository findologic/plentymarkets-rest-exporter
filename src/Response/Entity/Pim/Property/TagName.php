<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Translatable;

class TagName extends Entity implements Translatable
{
    private ?int $id;
    private ?int $tagId;
    private ?string $lang;
    private ?string $name;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->tagId = $this->getIntProperty('tagId', $data);
        $this->lang = $this->getStringProperty('tagLang', $data);
        $this->name = $this->getStringProperty('tagName', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'tagId' => $this->tagId,
            'tagLang' => $this->lang,
            'tagName' => $this->name,
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

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
