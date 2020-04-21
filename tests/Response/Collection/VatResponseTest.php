<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\VatResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration\VatRate;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class VatResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $vatResponse;

    public function setup(): void
    {
        $this->response = $this->getMockResponse('VatResponse/response.json');
        $this->vatResponse = VatParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'countryId' => 12
                ],
                'expectedId' => 2
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'vatRates' => [
                        'name' => 'A'
                    ]
                ],
                'expectedId' => 1
            ],
            'criteria with multiple sub-criteria' => [
                'criteria' => [
                    'vatRates' => [
                        'id' => 1,
                        'name' => 'F'
                    ]
                ],
                'expectedId' => 2
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'taxIdNumber' => 'ASDF456',
                    'vatRates' => [
                        'name' => 'I',
                        'vatRate' => 20.00
                    ]
                ],
                'expectedId' => 3
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $vatConfiguration = $this->vatResponse->findOne($criteria);

        $this->assertEquals($expectedId, $vatConfiguration->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(3, $this->vatResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'isRestrictedToDigitalItems' => false
        ];

        self::assertCount(2, $this->vatResponse->find($criteria));
    }

    public function testVatConfigurationDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $vatConfiguration = $this->vatResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $vatConfiguration->getData());

        $this->assertEquals(1, $vatConfiguration->getId());
        $this->assertEquals(10, $vatConfiguration->getCountryId());
        $this->assertEquals('DE123456789', $vatConfiguration->getTaxIdNumber());
        $this->assertEquals('2013-01-01T00:00:00+00:00', $vatConfiguration->getStartedAt());
        $this->assertEquals('2030-01-01T00:00:00+00:00', $vatConfiguration->getInvalidFrom());
        $this->assertEquals(1, $vatConfiguration->getLocationId());
        $this->assertEquals('none', $vatConfiguration->getMarginScheme());
        $this->assertEquals(false, $vatConfiguration->isRestrictedToDigitalItems());
        $this->assertEquals(false, $vatConfiguration->isStandard());
        $this->assertEquals('2017-12-21T13:16:02+00:00', $vatConfiguration->getCreatedAt());
        $this->assertEquals('2017-12-21T13:16:02+00:00', $vatConfiguration->getUpdatedAt());

        $vatRates = $vatConfiguration->getVatRates();
        $vatRate = reset($vatRates);
        $this->assertEquals(0, $vatRate->getId());
        $this->assertEquals('A', $vatRate->getName());
        $this->assertEquals(19.0, $vatRate->getVatRate());
    }
}
