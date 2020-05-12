<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Country;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Currency;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\CustomerClass;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Referrer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class SalesPrice extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $position;

    /** @var float */
    private $minimumOrderQuantity;

    /** @var string */
    private $type;

    /** @var bool */
    private $isCustomerPrice;

    /** @var bool */
    private $isDisplayedByDefault;

    /** @var bool */
    private $isLiveConversion;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /** @var string */
    private $interval;

    /** @var Name[] */
    private $names = [];

    /** @var array  */
    private $accounts = [];

    /** @var Country[] */
    private $countries = [];

    /** @var Currency[] */
    private $currencies = [];

    /** @var CustomerClass[] */
    private $customerClasses = [];

    /** @var Referrer[] */
    private $referrers = [];

    /** @var Client[] */
    private $clients = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->position = (int)$data['position'];
        $this->minimumOrderQuantity = (float)$data['minimumOrderQuantity'];
        $this->type = (string)$data['type'];
        $this->isCustomerPrice = (bool)$data['isCustomerPrice'];
        $this->isDisplayedByDefault = (bool)$data['isDisplayedByDefault'];
        $this->isLiveConversion = (bool)$data['isLiveConversion'];
        $this->createdAt = (string)$data['createdAt'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->interval = (string)$data['interval'];

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }

        $this->accounts = $data['accounts']; // Unknown structure - undocumented, received only empty arrays

        if (!empty($data['countries'])) {
            foreach ($data['countries'] as $country) {
                $this->countries[] = new Country($country);
            }
        }

        if (!empty($data['currencies'])) {
            foreach ($data['currencies'] as $currency) {
                $this->currencies[] = new Currency($currency);
            }
        }

        if (!empty($data['customerClasses'])) {
            foreach ($data['customerClasses'] as $customerClass) {
                $this->customerClasses[] = new CustomerClass($customerClass);
            }
        }

        if (!empty($data['referrers'])) {
            foreach ($data['referrers'] as $referrer) {
                $this->referrers[] = new Referrer($referrer);
            }
        }

        if (!empty($data['clients'])) {
            foreach ($data['clients'] as $client) {
                $this->clients[] = new Client($client);
            }
        }
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        $countries = [];
        foreach ($this->countries as $country) {
            $countries[] = $country->getData();
        }

        $currencies = [];
        foreach ($this->currencies as $currency) {
            $currencies[] = $currency->getData();
        }

        $customerClasses = [];
        foreach ($this->customerClasses as $customerClass) {
            $customerClasses[] = $customerClass->getData();
        }

        $referrers = [];
        foreach ($this->referrers as $referrer) {
            $referrers[] = $referrer->getData();
        }

        $clients = [];
        foreach ($this->clients as $client) {
            $clients[] = $client->getData();
        }

        return [
            'id' => $this->id,
            'position' => $this->position,
            'minimumOrderQuantity' => $this->minimumOrderQuantity,
            'type' => $this->type,
            'isCustomerPrice' => $this->isCustomerPrice,
            'isDisplayedByDefault' => $this->isDisplayedByDefault,
            'isLiveConversion' => $this->isLiveConversion,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'interval' => $this->interval,
            'names' => $names,
            'accounts' => $this->accounts,
            'countries' => $countries,
            'currencies' => $currencies,
            'customerClasses' => $customerClasses,
            'referrers' => $referrers,
            'clients' => $clients
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getMinimumOrderQuantity(): float
    {
        return $this->minimumOrderQuantity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isCustomerPrice(): bool
    {
        return $this->isCustomerPrice;
    }

    public function isDisplayedByDefault(): bool
    {
        return $this->isDisplayedByDefault;
    }

    public function isLiveConversion(): bool
    {
        return $this->isLiveConversion;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }

    public function getAccounts(): array
    {
        // Undocumented
        return $this->accounts;
    }

    /**
     * @return Country[]
     */
    public function getCountries(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->countries;
    }

    /**
     * @return Currency[]
     */
    public function getCurrencies(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->currencies;
    }

    /**
     * @return CustomerClass[]
     */
    public function getCustomerClasses(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->customerClasses;
    }

    /**
     * @return Referrer[]
     */
    public function getReferrers(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->referrers;
    }

    /**
     * @return Client[]
     */
    public function getClients(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->clients;
    }
}
