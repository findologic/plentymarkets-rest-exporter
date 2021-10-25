<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation\RelationValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PropertySelectionResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $selectionResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('PropertySelectionResponse/response.json');
        $this->selectionResponse = PropertySelectionParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'propertyId' => 4
                ],
                'expectedId' => 5
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'property' => [
                        'cast' => 'selection'
                    ]
                ],
                'expectedId' => 5
            ],
            'criteria with multi-level sub-criteria' => [
                'criteria' => [
                    'relation' => [
                        'relationValues' => [
                            'lang' => 'EN',
                            'value' => 'value1'
                        ]
                    ]
                ],
                'expectedId' => 8
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'property' => [
                        'cast' => 'selection'
                    ],
                    'relation' => [
                        'propertyId' => 4,
                        'relationValues' => [
                            'lang' => 'EN',
                            'value' => 'Very Large'
                        ]
                    ]
                ],
                'expectedId' => 5
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $salesPrice = $this->selectionResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(6, $this->selectionResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'propertyId' => 7
        ];

        self::assertCount(4, $this->selectionResponse->find($criteria));
    }

    public function testSelectionsDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $selection = $this->selectionResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $selection->getData());

        $this->assertEquals(9, $selection->getId());
        $this->assertEquals(7, $selection->getPropertyId());
        $this->assertEquals(0, $selection->getPosition());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $selection->getCreatedAt());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $selection->getUpdatedAt());

        /** @var Relation $relation */
        $relation = $selection->getRelation();
        $this->assertEquals(12, $relation->getId());
        $this->assertEquals(7, $relation->getPropertyId());
        $this->assertEquals('', $relation->getRelationTypeIdentifier());
        $this->assertEquals('', $relation->getRelationTargetId());
        $this->assertEquals(9, $relation->getSelectionRelationId());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $relation->getCreatedAt());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $relation->getUpdatedAt());

        $relationValues = $relation->getRelationValues();
        $this->assertCount(2, $relationValues);
        /** @var RelationValue $relationValue */
        $relationValue = reset($relationValues);
        $this->assertEquals(18, $relationValue->getId());
        $this->assertEquals(12, $relationValue->getPropertyRelationId());
        $this->assertEquals('EN', $relationValue->getLang());
        $this->assertEquals('value2', $relationValue->getValue());
        $this->assertEquals('', $relationValue->getDescription());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $relationValue->getCreatedAt());
        $this->assertEquals('2020-04-07T11:39:05+01:00', $relationValue->getUpdatedAt());

        $property = $selection->getProperty();
        $this->assertEquals(7, $property->getId());
        $this->assertEquals('multiSelection', $property->getCast());
        $this->assertEquals('item', $property->getTypeIdentifier());
        $this->assertEquals(5, $property->getPosition());
        $this->assertEquals(7, $property->getPropertyId());
        $this->assertEquals(1, $property->getPropertyGroupId());
        $this->assertEquals('2020-04-07T10:48:55+01:00', $property->getCreatedAt());
        $this->assertEquals('2020-04-07T10:48:55+01:00', $property->getUpdatedAt());
    }
}
