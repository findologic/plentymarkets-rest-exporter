<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

/**
 * Holds Plentymarkets-relevant configuration from the customer-login.
 */
class Config
{
    /** @var string */
    private $domain;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $language;

    /** @var int|null */
    private $multiShopId;

    /** @var int|null */
    private $availabilityId;

    /** @var int|null */
    private $priceId;

    /** @var int|null */
    private $rrpId;

    /** @var string  */
    private $protocol = Client::PROTOCOL_HTTPS;

    /** @var bool */
    private $debug = false;

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

    public static function parseByCustomerLoginResponse(array $data, bool $debug = false): self
    {
        $shop = array_values($data)[0];

        return new Config([
            'domain' => $shop['url'],
            'username' => $shop['export_username'],
            'password' => $shop['export_password'],
            'language' => $shop['language'],
            'multiShopId' => $shop['multishop_id'],
            'availabilityId' => $shop['availability_id'],
            'priceId' => $shop['price_id'],
            'rrpId' => $shop['rrp_id'],
            'debug' => $debug
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
}
