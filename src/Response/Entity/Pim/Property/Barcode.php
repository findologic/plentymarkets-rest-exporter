<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Barcode extends Entity
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('barcodeId', $data);
        $this->code = $this->getStringProperty('code', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
