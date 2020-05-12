<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration\VatRate;

class VatConfiguration extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $countryId;

    /** @var string */
    private $taxIdNumber;

    /** @var string */
    private $startedAt;

    /** @var string|null */
    private $invalidFrom;

    /** @var int */
    private $locationId;

    /** @var string */
    private $marginScheme;

    /** @var bool */
    private $isRestrictedToDigitalItems;

    /** @var bool */
    private $isStandard;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /** @var VatRate[] */
    private $vatRates = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->countryId = (int)$data['countryId'];
        $this->taxIdNumber = (string)$data['taxIdNumber'];
        $this->startedAt = (string)$data['startedAt'];
        $this->invalidFrom = !is_null($data['invalidFrom']) ? (string)$data['invalidFrom'] : null;
        $this->locationId = (int)$data['locationId'];
        $this->marginScheme = (string)$data['marginScheme'];
        $this->isRestrictedToDigitalItems = (bool)$data['isRestrictedToDigitalItems'];
        // documentated as bool, however nothing was returned during testing
        $this->isStandard = (bool)($data['isStandard'] ?? false);
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];

        if (!empty($data['vatRates'])) {
            foreach ($data['vatRates'] as $vatRate) {
                $this->vatRates[] = new VatRate($vatRate);
            }
        }
    }

    public function getData(): array
    {
        $vatRates = [];
        foreach ($this->vatRates as $vatRate) {
            $vatRates[] = $vatRate->getData();
        }

        return [
            'id' => $this->id,
            'countryId' => $this->countryId,
            'taxIdNumber' => $this->taxIdNumber,
            'startedAt' => $this->startedAt,
            'invalidFrom' => $this->invalidFrom,
            'locationId' => $this->locationId,
            'marginScheme' => $this->marginScheme,
            'isRestrictedToDigitalItems' => $this->isRestrictedToDigitalItems,
            'isStandard' => $this->isStandard,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'vatRates' => $vatRates
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function getTaxIdNumber(): string
    {
        return $this->taxIdNumber;
    }

    public function getStartedAt(): string
    {
        return $this->startedAt;
    }

    public function getInvalidFrom(): ?string
    {
        return $this->invalidFrom;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function getMarginScheme(): string
    {
        return $this->marginScheme;
    }

    public function isRestrictedToDigitalItems(): bool
    {
        return $this->isRestrictedToDigitalItems;
    }

    public function isStandard(): bool
    {
        return $this->isStandard;
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
     * @return VatRate[]
     */
    public function getVatRates(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->vatRates;
    }
}
