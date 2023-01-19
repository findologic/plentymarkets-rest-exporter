<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertyGroupResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PropertyGroupResponseTest extends TestCase
{
    use ResponseHelper;

    private Response $response;

    private PropertyGroupResponse $propertyGroupResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('PropertyGroupResponse/response.json');
        $this->propertyGroupResponse = PropertyGroupParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'id' => 2
                ],
                'expectedId' => 2
            ],
            'sub-criteria' => [
                'criteria' => [
                    'names' => [
                        'name' => 'Attributes'
                    ]
                ],
                'expectedId' => 1
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $propertyGroup = $this->propertyGroupResponse->findOne($criteria);

        $this->assertEquals($expectedId, $propertyGroup->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(2, $this->propertyGroupResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'names' => [
                'lang' => 'de'
            ]
        ];

        self::assertCount(2, $this->propertyGroupResponse->find($criteria));
    }

    public function testPropertyGroupDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $propertyGroup = $this->propertyGroupResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $propertyGroup->getData());

        $this->assertEquals(1, $propertyGroup->getId());
        $this->assertEquals(0, $propertyGroup->getPosition());
        $this->assertEquals('2021-07-15T11:35:07+01:00', $propertyGroup->getCreatedAt());
        $this->assertEquals('2021-07-15T11:35:07+01:00', $propertyGroup->getUpdatedAt());

        $names = $propertyGroup->getNames();

        $this->assertCount(2, $names);
        $name = $names[0];

        $this->assertSame(1, $name->getId());
        $this->assertSame(1, $name->getGroupId());
        $this->assertSame('Attributes', $name->getName());
        $this->assertSame('en', $name->getLang());
        $this->assertSame('', $name->getDescription());
        $this->assertSame('2021-07-15T11:35:07+01:00', $name->getCreatedAt());
        $this->assertSame('2021-07-15T11:35:07+01:00', $name->getUpdatedAt());
    }
}
