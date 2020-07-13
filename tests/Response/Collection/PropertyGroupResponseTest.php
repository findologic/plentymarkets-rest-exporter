<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PropertyGroupResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $propertyGroupResponse;

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
                    'backendName' => 'Test Group 2'
                ],
                'expectedId' => 2
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'names' => [
                        'description' => 'description 1'
                    ]
                ],
                'expectedId' => 1
            ],
            'mixed-level criteria' => [
                'criteria' => [
                    'backendName' => 'Test Group 2',
                    'names' => [
                        'propertyGroupId' => 2
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
        $property = $this->propertyGroupResponse->findOne($criteria);

        $this->assertEquals($expectedId, $property->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(2, $this->propertyGroupResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = ['ottoComponent' => 0];

        self::assertCount(2, $this->propertyGroupResponse->find($criteria));
    }

    public function testPropertyGroupDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $propertyGroup = $this->propertyGroupResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $propertyGroup->getData());

        $this->assertEquals(1, $propertyGroup->getId());
        $this->assertEquals('Test Group', $propertyGroup->getBackendName());
        $this->assertEquals('single', $propertyGroup->getOrderPropertyGroupingType());
        $this->assertFalse($propertyGroup->isSurchargePercental());
        $this->assertEquals(0, $propertyGroup->getOttoComponent());
        $this->assertEquals('2018-06-04T07:06:37+01:00', $propertyGroup->getUpdatedAt());

        $names = $propertyGroup->getNames();
        $this->assertCount(1, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getPropertyGroupId());
        $this->assertEquals('Test Group', $name->getName());
        $this->assertEquals('en', $name->getLang());
        $this->assertEquals('description 1', $name->getDescription());
    }
}
