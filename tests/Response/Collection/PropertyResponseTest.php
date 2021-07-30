<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Amazon;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group\GroupRelation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Group\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Name as PropertyName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Option;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Relation\RelationValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PropertyResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $propertyResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('PropertyResponse/response.json');
        $this->propertyResponse = PropertyParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'cast' => 'int'
                ],
                'expectedId' => 5
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'names' => [
                        'description' => 'Size'
                    ]
                ],
                'expectedId' => 4
            ],
            'criteria with multi-level sub-criteria' => [
                'criteria' => [
                    'selections' => [
                        'relation' => [
                            'relationValues' => [
                                'lang' => 'de',
                                'value' => 'value2'
                            ]
                        ]
                    ]
                ],
                'expectedId' => 7
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'cast' => 'text',
                    'names' => [
                        'lang' => 'en',
                        'name' => 'Description'
                    ],
                    'amazons' => [
                        'category' => 'ConsumerElectronics',
                        'field' => 'feed_product_type'
                    ]
                ],
                'expectedId' => 6
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $property = $this->propertyResponse->findOne($criteria);

        $this->assertEquals($expectedId, $property->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(6, $this->propertyResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'cast' => 'selection'
        ];

        self::assertCount(2, $this->propertyResponse->find($criteria));
    }

    public function testPropertyDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $property = $this->propertyResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $property->getData());

        $this->assertEquals(1, $property->getId());
        $this->assertEquals('selection', $property->getCast());
        $this->assertEquals('item', $property->getTypeIdentifier());
        $this->assertEquals(0, $property->getPosition());
        $this->assertEquals('2018-04-16T07:04:49+01:00', $property->getCreatedAt());
        $this->assertEquals('2018-04-16T07:04:49+01:00', $property->getUpdatedAt());
        $this->assertEquals('1', $property->getPropertyId());
        $this->assertEquals('1', $property->getPropertyGroupId());

        $groups = $property->getGroups();
        $this->assertCount(1, $groups);
        /** @var Group $group */
        $group = reset($groups);
        $this->assertEquals(1, $group->getId());
        $this->assertEquals(0, $group->getPosition());
        $this->assertEquals('2018-04-16T07:08:03+01:00', $group->getCreatedAt());
        $this->assertEquals('2018-04-16T07:08:03+01:00', $group->getUpdatedAt());

        $names = $group->getNames();
        $this->assertCount(2, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getId());
        $this->assertEquals(1, $name->getPropertyGroupId());
        $this->assertEquals('en', $name->getLang());
        $this->assertEquals('Attributes', $name->getName());
        $this->assertEquals('', $name->getDescription());
        $this->assertEquals('2018-04-16T07:08:03+01:00', $name->getCreatedAt());
        $this->assertEquals('2019-02-22T14:34:59+00:00', $name->getUpdatedAt());

        /** @var GroupRelation $groupRelation */
        $groupRelation = $group->getGroupRelation();
        $this->assertEquals(1, $groupRelation->getPropertyId());
        $this->assertEquals(2, $groupRelation->getPropertyGroupId());

        $this->assertIsArray($property->getAvailabilities());
        $this->assertEmpty($property->getAvailabilities());

        $names = $property->getNames();
        $this->assertCount(1, $names);
        /** @var PropertyName $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getId());
        $this->assertEquals(1, $name->getPropertyId());
        $this->assertEquals('en', $name->getLang());
        $this->assertEquals('Color', $name->getName());
        $this->assertEquals('Color', $name->getDescription());
        $this->assertEquals('2018-04-16T07:04:49+01:00', $name->getCreatedAt());
        $this->assertEquals('2018-04-16T07:04:49+01:00', $name->getUpdatedAt());

        $options = $property->getOptions();
        $this->assertCount(4, $options);
        /** @var Option $option */
        $option = reset($options);
        $this->assertEquals(2, $option->getId());
        $this->assertEquals(1, $option->getPropertyId());
        $this->assertEquals('display', $option->getTypeOptionIdentifier());
        $this->assertEquals('2018-04-16T07:05:22+01:00', $option->getCreatedAt());
        $this->assertEquals('2018-04-16T07:05:22+01:00', $option->getUpdatedAt());

        $this->assertIsArray($property->getMarkets());
        $this->assertEmpty($property->getMarkets());

        $selections = $property->getSelections();
        $this->assertCount(2, $selections);
        /** @var Selection $selection */
        $selection = reset($selections);
        $this->assertEquals(1, $selection->getId());
        $this->assertEquals(1, $selection->getPropertyId());
        $this->assertEquals(0, $selection->getPosition());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $selection->getCreatedAt());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $selection->getUpdatedAt());

        /** @var Relation $relation */
        $relation = $selection->getRelation();
        $this->assertEquals(1, $relation->getId());
        $this->assertEquals(1, $relation->getPropertyId());
        $this->assertEquals('', $relation->getRelationTypeIdentifier());
        $this->assertEquals('', $relation->getRelationTargetId());
        $this->assertEquals(1, $relation->getSelectionRelationId());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $relation->getCreatedAt());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $relation->getUpdatedAt());

        $relationValues = $relation->getRelationValues();
        $this->assertCount(2, $relationValues);
        /** @var RelationValue $relationValue */
        $relationValue = reset($relationValues);
        $this->assertEquals(1, $relationValue->getId());
        $this->assertEquals(1, $relationValue->getPropertyRelationId());
        $this->assertEquals('EN', $relationValue->getLang());
        $this->assertEquals('Red', $relationValue->getValue());
        $this->assertEquals('Red', $relationValue->getDescription());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $relationValue->getCreatedAt());
        $this->assertEquals('2018-04-16T07:05:34+01:00', $relationValue->getUpdatedAt());

        $amazons = $property->getAmazons();
        $this->assertCount(1, $amazons);
        /** @var Amazon $amazon */
        $amazon = reset($amazons);
        $this->assertEquals(2, $amazon->getId());
        $this->assertEquals(1, $amazon->getPropertyId());
        $this->assertEquals('de', $amazon->getPlatform());
        $this->assertEquals('AutoAccessory', $amazon->getCategory());
        $this->assertEquals('item_name', $amazon->getField());
        $this->assertEquals('2019-02-22T14:01:21+00:00', $amazon->getCreatedAt());
        $this->assertEquals('2019-02-22T14:01:21+00:00', $amazon->getUpdatedAt());
    }
}
