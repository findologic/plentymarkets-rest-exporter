<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class AttributesResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $attributesResponse;

    public function setup(): void
    {
        $this->response = $this->getMockResponse('AttributesResponse/response.json');
        $this->attributesResponse = AttributesParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'backendName' => 'Test'
                ],
                'expectedId' => 2
            ],
            'multjple simple criteria' => [
                'criteria' => [
                    'backendName' => 'Test',
                    'typeOfSelectionInOnlineStore' => 'dropdown'
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
        $salesPrice = $this->attributesResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(2, $this->attributesResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'position' => 1
        ];

        self::assertCount(1, $this->attributesResponse->find($criteria));
    }

    public function testAttributeDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $attribute = $this->attributesResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $attribute->getData());

        $this->assertEquals(1, $attribute->getId());
        $this->assertEquals('Couch color', $attribute->getBackendName());
        $this->assertEquals(1, $attribute->getPosition());
        $this->assertEquals(false, $attribute->isSurchargePercental());
        $this->assertEquals(true, $attribute->isLinkableToImage());
        $this->assertEquals('', $attribute->getAmazonAttribute());
        $this->assertEquals('color', $attribute->getFruugoAttribute());
        $this->assertEquals(0, $attribute->getPixmaniaAttribute());
        $this->assertEquals('', $attribute->getOttoAttribute());
        $this->assertEquals('', $attribute->getGoogleShoppingAttribute());
        $this->assertEquals(0, $attribute->getNeckermannAtEpAttribute());
        $this->assertEquals('dropdown', $attribute->getTypeOfSelectionInOnlineStore());
        $this->assertEquals(0, $attribute->getLaRedouteAttribute());
        $this->assertEquals(false, $attribute->isGroupable());
        $this->assertEquals('2015-04-30T08:56:34+01:00', $attribute->getUpdatedAt());
    }
}
