<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Client extends Entity
{
    /** @var int */
    private $plentyId;

    public function __construct(array $data)
    {
        $this->plentyId = $this->getIntProperty('plentyId', $data);
    }

    public function getData(): array
    {
        return [
            'plentyId' => $this->plentyId
        ];
    }

    public function getPlentyId(): int
    {
        return $this->plentyId;
    }
}
