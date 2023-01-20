<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ManufacturerResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ManufacturerResponseTest extends TestCase
{
    use ResponseHelper;

    private Response $response;

    private ManufacturerResponse $manufacturerResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('ManufacturerResponse/response.json');
        $this->manufacturerResponse = ManufacturerParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'externalName' => 'externalNameB'
                ],
                'expectedId' => 2
            ],
            'multjple simple criteria' => [
                'criteria' => [
                    'name' => 'nameC',
                    'externalName' => 'externalNameC'
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
        $salesPrice = $this->manufacturerResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(3, $this->manufacturerResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'countryId' => 1
        ];

        self::assertCount(2, $this->manufacturerResponse->find($criteria));
    }

    public function testManufacturerDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $manufacturer = $this->manufacturerResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $manufacturer->getData());

        $this->assertEquals(1, $manufacturer->getId());
        $this->assertEquals('nameA', $manufacturer->getName());
        $this->assertEquals('externalNameA', $manufacturer->getExternalName());
        $this->assertEquals('logoA', $manufacturer->getLogo());
        $this->assertEquals('urlA', $manufacturer->getUrl());
        $this->assertEquals('streetA', $manufacturer->getStreet());
        $this->assertEquals('houseNoA', $manufacturer->getHouseNo());
        $this->assertEquals('postcodeA', $manufacturer->getPostcode());
        $this->assertEquals('townA', $manufacturer->getTown());
        $this->assertEquals('phoneNumberA', $manufacturer->getPhoneNumber());
        $this->assertEquals('faxNumberA', $manufacturer->getFaxNumber());
        $this->assertEquals('emailA', $manufacturer->getEmail());
        $this->assertEquals(2, $manufacturer->getCountryId());
        $this->assertEquals(2, $manufacturer->getPixmaniaBrandId());
        $this->assertEquals(3, $manufacturer->getNeckermannBrandId());
        $this->assertEquals(4, $manufacturer->getNeckermannAtEpBrandId());
        $this->assertEquals(5, $manufacturer->getLaRedouteBrandId());
        $this->assertEquals(6, $manufacturer->getPosition());
        $this->assertEquals('commentA', $manufacturer->getComment());
        $this->assertEquals('updatedAtA', $manufacturer->getUpdatedAt());
    }
}
