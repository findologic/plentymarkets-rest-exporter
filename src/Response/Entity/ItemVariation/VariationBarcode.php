<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class VariationBarcode extends Entity
{
    /** @var int */
    private $variationId;

    /** @var int */
    private $barcodeId;

    /** @var string */
    private $code;

    /** @var string */
    private $createdAt;

    public function __construct(array $data)
    {
        $this->variationId = (int)$data['variationId'];
        $this->barcodeId = (int)$data['barcodeId'];
        $this->code = (string)$data['code'];
        $this->createdAt = (string)$data['createdAt'];
    }

    public function getData(): array
    {
        return [
            'variationId' => $this->variationId,
            'barcodeId' => $this->barcodeId,
            'code' => $this->code,
            'createdAt' => $this->createdAt
        ];
    }

    public function getVariationId(): int
    {
        return $this->variationId;
    }

    public function getBarcodeId(): int
    {
        return $this->barcodeId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
