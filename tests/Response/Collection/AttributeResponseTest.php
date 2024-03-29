<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributeResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AttributeResponseTest extends TestCase
{
    use ResponseHelper;

    private Response $response;

    private AttributeResponse $attributeResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('AttributeResponse/response.json');
        $this->attributeResponse = AttributeParser::parse($this->response);
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
            'multiple simple criteria' => [
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
        $salesPrice = $this->attributeResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(3, $this->attributeResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'position' => 1
        ];

        self::assertCount(1, $this->attributeResponse->find($criteria));
    }

    public function testAttributeDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $attribute = $this->attributeResponse->first();

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

        $names = $attribute->getNames();
        $this->assertCount(2, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getAttributeId());
        $this->assertEquals('de', $name->getLang());
        $this->assertEquals('couch color de', $name->getName());
    }
}
