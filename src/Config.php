<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use Exception;

/**
 * Holds Plentymarkets-relevant configuration from the customer-login.
 */
class Config
{
    private string $domain;

    private string $username;

    private string $password;

    private string $language;

    private ?int $multiShopId = null;

    private ?int $availabilityId = null;

    private ?int $priceId = null;

    private ?int $rrpId = null;

    private string $protocol = Client::PROTOCOL_HTTPS;

    private bool $debug = false;

    private bool $exportUnavailableVariations = false;

    private bool $exportOrdernumberProductId = true;

    private bool $exportOrdernumberVariantId = true;

    private bool $exportOrdernumberVariantNumber = true;

    private bool $exportOrdernumberVariantModel = true;

    private bool $exportOrdernumberVariantBarcodes = true;

    public function __construct(array $rawConfig = [])
    {
        foreach ($rawConfig as $configKey => $configValue) {
            $setter = 'set' . ucfirst($configKey);

            if (!method_exists($this, $setter)) {
                continue;
            }

            $this->{$setter}($configValue);
        }
    }

    public static function fromArray(array $data, bool $debug = false): self
    {
        $shop = array_values($data)[0] ?? null;
        if (!$shop || !isset($shop['plentymarkets'])) {
            throw new Exception('Something went wrong while tying to fetch the importer data');
        }

        $plentyConfig = $shop['plentymarkets'];
        return new Config([
            'domain' => $shop['url'],
            'username' => $shop['export_username'],
            'password' => $shop['export_password'],
            'language' => $shop['language'],
            'multiShopId' => $plentyConfig['multishop_id'],
            'availabilityId' => $plentyConfig['availability_id'],
            'priceId' => $plentyConfig['price_id'],
            'rrpId' => $plentyConfig['rrp_id'],
            'exportUnavailableVariations' => $plentyConfig['export_unavailable_variants'] ?? true,
            'exportOrdernumberProductId' => $plentyConfig['export_ordernumber_product_id'] ?? true,
            'exportOrdernumberVariantId' => $plentyConfig['export_ordernumber_variant_id'] ?? true,
            'exportOrdernumberVariantNumber' => $plentyConfig['export_ordernumber_variant_number'] ?? true,
            'exportOrdernumberVariantModel' => $plentyConfig['export_ordernumber_variant_model'] ?? true,
            'exportOrdernumberVariantBarcodes' => $plentyConfig['export_ordernumber_variant_barcodes'] ?? true,
            'debug' => $debug
        ]);
    }

    public static function fromEnvironment(): Config
    {
        return new Config([
            'domain' => Utils::env('EXPORT_DOMAIN'),
            'username' => Utils::env('EXPORT_USERNAME'),
            'password' => Utils::env('EXPORT_PASSWORD'),
            'language' => Utils::env('EXPORT_LANGUAGE'),
            'multiShopId' => (int)Utils::env('EXPORT_MULTISHOP_ID'),
            'availabilityId' => (int)Utils::env('EXPORT_AVAILABILITY_ID'),
            'priceId' => (int)Utils::env('EXPORT_PRICE_ID'),
            'rrpId' => (int)Utils::env('EXPORT_RRP_ID'),
            'exportUnavailableVariations' => (bool)Utils::env('EXPORT_UNAVAILABLE_VARIATIONS'),
            'exportOrdernumberProductId' => (bool)Utils::env('EXPORT_ORDERNUMBER_PRODUCT_ID', true),
            'exportOrdernumberVariantId' => (bool)Utils::env('EXPORT_ORDERNUMBER_VARIANT_ID', true),
            'exportOrdernumberVariantNumber' => (bool)Utils::env('EXPORT_ORDERNUMBER_VARIANT_NUMBER', true),
            'exportOrdernumberVariantModel' => (bool)Utils::env('EXPORT_ORDERNUMBER_VARIANT_MODEL', true),
            'exportOrdernumberVariantBarcodes' => (bool)Utils::env('EXPORT_ORDERNUMBER_VARIANT_BARCODES', true),
            'debug' => (bool)Utils::env('DEBUG')
        ]);
    }

    /**
     * A domain or any URI can be submitted to this method. The configuration may only store the domain name itself.
     *
     * @param string $uri
     * @return $this
     */
    public function setDomain(string $uri): self
    {
        // TODO: Only fetch the domain name out of the given URI.
        $this->domain = $uri;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setLanguage(string $language): self
    {
        // TODO: Get the language table out of the old plenty-rest exporter and store it here.
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }


    public function setMultiShopId(?int $multiShopId): self
    {
        $this->multiShopId = $multiShopId;

        return $this;
    }

    public function getMultiShopId(): ?int
    {
        return $this->multiShopId;
    }

    public function setAvailabilityId(?int $availabilityId): self
    {
        $this->availabilityId = $availabilityId;

        return $this;
    }

    public function getAvailabilityId(): ?int
    {
        return $this->availabilityId;
    }

    public function setPriceId(?int $priceId): self
    {
        $this->priceId = $priceId;

        return $this;
    }

    public function getPriceId(): ?int
    {
        return $this->priceId;
    }

    public function setRrpId(?int $rrpId): self
    {
        $this->rrpId = $rrpId;

        return $this;
    }

    public function getRrpId(): ?int
    {
        return $this->rrpId;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function isExportUnavailableVariations(): bool
    {
        return $this->exportUnavailableVariations;
    }

    public function setExportUnavailableVariations(bool $exportUnavailableVariations): void
    {
        $this->exportUnavailableVariations = $exportUnavailableVariations;
    }

    public function getExportOrdernumberProductId(): bool
    {
        return $this->exportOrdernumberProductId;
    }

    public function setExportOrdernumberProductId(bool $exportOrdernumberProductId): void
    {
        $this->exportOrdernumberProductId = $exportOrdernumberProductId;
    }

    public function getExportOrdernumberVariantId(): bool
    {
        return $this->exportOrdernumberVariantId;
    }

    public function setExportOrdernumberVariantId(bool $exportOrdernumberVariantId): void
    {
        $this->exportOrdernumberVariantId = $exportOrdernumberVariantId;
    }

    public function getExportOrdernumberVariantNumber(): bool
    {
        return $this->exportOrdernumberVariantNumber;
    }

    public function setExportOrdernumberVariantNumber(bool $exportOrdernumberVariantNumber): void
    {
        $this->exportOrdernumberVariantNumber = $exportOrdernumberVariantNumber;
    }

    public function getExportOrdernumberVariantModel(): bool
    {
        return $this->exportOrdernumberVariantModel;
    }

    public function setExportOrdernumberVariantModel(bool $exportOrdernumberVariantModel): void
    {
        $this->exportOrdernumberVariantModel = $exportOrdernumberVariantModel;
    }

    public function getExportOrdernumberVariantBarcodes(): bool
    {
        return $this->exportOrdernumberVariantBarcodes;
    }

    public function setExportOrdernumberVariantBarcodes(bool $exportOrdernumberVariantBarcodes): void
    {
        $this->exportOrdernumberVariantBarcodes = $exportOrdernumberVariantBarcodes;
    }
}
