<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Unit extends Entity
{
    private ?int $unitId;
    private ?int $unitCombinationId;
    private ?string $content;

    public function __construct(array $data)
    {
        $this->unitId = $this->getIntProperty('unitId', $data);
        $this->unitCombinationId = $this->getIntProperty('unitCombinationId', $data);
        $this->content = $this->getStringProperty('content', $data);
    }

    public function getData(): array
    {
        return [
            'unitId' => $this->unitId,
            'unitCombinationId' => $this->unitCombinationId,
            'content' => $this->content
        ];
    }

    public function getUnitId(): ?int
    {
        return $this->unitId;
    }

    public function getUnitCombinationId(): ?int
    {
        return $this->unitCombinationId;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }
}
