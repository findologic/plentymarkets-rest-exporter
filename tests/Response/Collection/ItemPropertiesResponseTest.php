<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertiesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemPropertiesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class ItemPropertiesResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $propertiesResponse;

    public function setup(): void
    {
        $this->response = $this->getMockResponse('ItemPropertiesResponse/response.json');
        $this->propertiesResponse = ItemPropertiesParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'unit' => 'AA'
                ],
                'expectedId' => 3
            ],
            'multjple simple criteria' => [
                'criteria' => [
                    'unit' => 'KGM',
                    'backendName' => 'Second Property'
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
        $salesPrice = $this->propertiesResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(3, $this->propertiesResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'unit' => 'KGM'
        ];

        self::assertCount(2, $this->propertiesResponse->find($criteria));
    }

    public function testPropertyDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $property = $this->propertiesResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $property->getData());

        $this->assertEquals(1, $property->getId());
        $this->assertEquals(0, $property->getPosition());
        $this->assertEquals(0, $property->getPropertyGroupId());
        $this->assertEquals('KGM', $property->getUnit());
        $this->assertEquals('Test', $property->getBackendName());
        $this->assertEquals('A', $property->getComment());
        $this->assertEquals('int', $property->getValueType());
        $this->assertEquals(true, $property->isSearchable());
        $this->assertEquals(false, $property->isOderProperty());
        $this->assertEquals(true, $property->isShownOnItemPage());
        $this->assertEquals(true, $property->isShownOnItemList());
        $this->assertEquals(true, $property->isShownAtCheckout());
        $this->assertEquals(true, $property->isShownInPdf());
        $this->assertEquals(false, $property->isShownAsAdditionalCosts());
        $this->assertEquals(0.0, $property->getSurcharge());
        $this->assertEquals('2019-02-22T13:57:13+00:00', $property->getUpdatedAt());
    }
}
