<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPricesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\SalesPricesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Country;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Currency;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\CustomerClass;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice\Referrer;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class SalesPriceResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $salesPriceResponse;

    public function setup(): void
    {
        $this->response = $this->getMockResponse('SalesPricesResponse/response.json');
        $this->salesPriceResponse = SalesPricesParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'type' => 'default'
                ],
                'expectedId' => 1
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'clients' => [
                        'plentyId' => 42
                    ]
                ],
                'expectedId' => 3
            ],
            'criteria with multiple sub-criteria' => [
                'criteria' => [
                    'clients' => [
                        'plentyId' => 42,
                        'createdAt' => '2016-09-06T10:02:02+01:00'
                    ]
                ],
                'expectedId' => 3
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'type' => 'rrp',
                    'clients' => [
                        'plentyId' => 44,
                        'createdAt' => '2016-09-06T10:02:46+01:00'
                    ]
                ],
                'expectedId' => 2
            ],
            'simple criteria with multiple different sub-criteria' => [
                'criteria' => [
                    'type' => 'rrp',
                    'clients' => [
                        'plentyId' => 44,
                        'createdAt' => '2016-09-06T10:02:46+01:00'
                    ],
                    'referrers' => [
                        'salesPriceId' => 3
                    ]
                ],
                'expectedId' => 2
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $salesPrice = $this->salesPriceResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(3, $this->salesPriceResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'isLiveConversion' => true
        ];

        self::assertCount(2, $this->salesPriceResponse->find($criteria));
    }

    public function testSalesPriceDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $salesPrice = $this->salesPriceResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $salesPrice->getData());

        $this->assertEquals(1, $salesPrice->getId());
        $this->assertEquals(0, $salesPrice->getPosition());
        $this->assertEquals(1, $salesPrice->getMinimumOrderQuantity());
        $this->assertEquals('default', $salesPrice->getType());
        $this->assertEquals(false, $salesPrice->isCustomerPrice());
        $this->assertEquals(true, $salesPrice->isDisplayedByDefault());
        $this->assertEquals(false, $salesPrice->isLiveConversion());
        $this->assertEquals('2016-09-05 12:24:53', $salesPrice->getCreatedAt());
        $this->assertEquals('2016-09-06 10:02:02', $salesPrice->getUpdatedAt());
        $this->assertEquals('none', $salesPrice->getInterval());

        $names = $salesPrice->getNames();
        $this->assertCount(2, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getSalesPriceId());
        $this->assertEquals('de', $name->getLang());
        $this->assertEquals('Preis', $name->getNameInternal());
        $this->assertEquals('Preis', $name->getNameExternal());
        $this->assertEquals('2016-09-05T12:24:53+01:00', $name->getCreatedAt());
        $this->assertEquals('2016-09-05T13:46:34+01:00', $name->getUpdatedAt());

        $this->assertIsArray($salesPrice->getAccounts());
        $this->assertCount(0, $salesPrice->getAccounts());

        $countries = $salesPrice->getCountries();
        $this->assertCount(1, $countries);
        /** @var Country $country */
        $country = reset($countries);
        $this->assertEquals(1, $country->getSalesPriceId());
        $this->assertEquals(-1, $country->getCountryId());
        $this->assertEquals('2016-09-06T10:02:05+01:00', $country->getCreatedAt());
        $this->assertEquals('2016-09-06T10:02:05+01:00', $country->getUpdatedAt());

        $currencies = $salesPrice->getCurrencies();
        $this->assertCount(1, $currencies);
        /** @var Currency $currency */
        $currency = reset($currencies);
        $this->assertEquals(1, $currency->getSalesPriceId());
        $this->assertEquals('-1', $currency->getCurrency());
        $this->assertEquals('2016-09-06T10:02:05+01:00', $currency->getCreatedAt());
        $this->assertEquals('2016-09-06T10:02:05+01:00', $currency->getUpdatedAt());

        $customerClasses = $salesPrice->getCustomerClasses();
        $this->assertCount(1, $customerClasses);
        /** @var CustomerClass $customerClass */
        $customerClass = reset($customerClasses);
        $this->assertEquals(1, $customerClass->getSalesPriceId());
        $this->assertEquals(-1, $customerClass->getCustomerClassId());
        $this->assertEquals('2016-09-06T10:02:02+01:00', $customerClass->getCreatedAt());
        $this->assertEquals('2016-09-06T10:02:02+01:00', $customerClass->getUpdatedAt());

        $referrers = $salesPrice->getReferrers();
        $this->assertCount(3, $referrers);
        /** @var Referrer $referrer */
        $referrer = reset($referrers);
        $this->assertEquals(1, $referrer->getSalesPriceId());
        $this->assertEquals(0, $referrer->getReferrerId());
        $this->assertEquals('2016-09-06T10:02:04+01:00', $referrer->getCreatedAt());
        $this->assertEquals('2016-09-06T10:02:04+01:00', $referrer->getUpdatedAt());

        $clients = $salesPrice->getClients();
        $this->assertCount(1, $clients);
        /** @var Client $client */
        $client = reset($clients);
        $this->assertEquals(1, $client->getSalesPriceId());
        $this->assertEquals(-1, $client->getPlentyId());
        $this->assertEquals('2016-09-06T10:02:02+01:00', $client->getCreatedAt());
        $this->assertEquals('2016-09-06T10:02:02+01:00', $client->getUpdatedAt());
    }
}
