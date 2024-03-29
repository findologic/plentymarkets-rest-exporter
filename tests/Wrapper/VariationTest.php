<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute as AttributeEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty as CharacteristicEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property as PropertyEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Variation as VariationWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{
    use ResponseHelper;
    use ConfigHelper;

    private Registry|MockObject $registryMock;

    private Config $defaultConfig;

    private RegistryService|MockObject $registryServiceMock;

    public function setUp(): void
    {
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $webstoreResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $parsedWebstoreResponse = WebStoreParser::parse($webstoreResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getWebstore')
            ->willReturn($parsedWebstoreResponse->first());

        $this->defaultConfig = $this->getDefaultConfig();
        $this->defaultConfig->setLanguage('en');
    }

    public function testVariationWrap(): void
    {
        $categoryResponse = $this->getMockResponse('CategoryResponse/one.json');
        $parsedCategoryResponse = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getCategory')
            ->willReturn($parsedCategoryResponse->first());

        $attributeResponse = $this->getMockResponse('AttributeResponse/one.json');
        $parsedAttributeResponse = AttributeParser::parse($attributeResponse);

        $unitResponse = $this->getMockResponse('UnitResponse/response.json');
        $parsedUnitResponse = UnitParser::parse($unitResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($parsedAttributeResponse->first());

        $this->registryServiceMock->expects($this->any())
            ->method('getUnit')
            ->with(4)
            ->willReturn($parsedUnitResponse->findOne(['id' => 4]));

        $variationEntity = $this->getVariationEntity('Pim/Variations/response.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertTrue($wrapper->isMain());
        $this->assertEquals(0, $wrapper->getPosition());
        $this->assertEquals(0, $wrapper->getVatId());
        $this->assertEquals('S-000813-C', $wrapper->getNumber());
        $this->assertEquals('modeeeel', $wrapper->getModel());
        $this->assertEquals(1004, $wrapper->getId());
        $this->assertEquals(106, $wrapper->getItemId());
        $this->assertEquals(['3213213213213'], $wrapper->getBarcodes());
        $this->assertEquals(279, $wrapper->getPrice());
        $this->assertEquals('milligram', $wrapper->getBaseUnit());
        $this->assertEquals('1000', $wrapper->getPackageSize());

        $attributes = $wrapper->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertEquals('cat', $attributes[0]->getKey());
        $this->assertEquals(['Armchairs & Stools'], $attributes[0]->getValues());
        $this->assertEquals('cat_url', $attributes[1]->getKey());
        $this->assertEquals(['/wohnzimmer/sessel-hocker/'], $attributes[1]->getValues());
        $this->assertEquals('couch color en', $attributes[2]->getKey());
        $this->assertEquals(['purple'], $attributes[2]->getValues());

        $properties = $wrapper->getProperties();
        $this->assertEmpty($properties);
    }

    public function testAttributesOverLengthLimitAreNotExported(): void
    {
        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $parsedAttributeResponse = AttributeParser::parse($attributeResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getAttribute')
            ->willReturnOnConsecutiveCalls(
                $parsedAttributeResponse->first(),
                $parsedAttributeResponse->findOne(['id' => 2])
            );

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variation_with_one_attribute_over_length_limit.json'
        );

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();

        $attributes = $wrapper->getAttributes();
        $this->assertCount(1, $attributes);
    }

    public function testVariationWithoutTranslatedPropertyIsSkipped(): void
    {
        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variation_with_not_translated_property_response.json'
        );

        $propertyEntity = PropertyParser::parse($this->getMockResponse(
            'PropertyResponse/property_without_translated_name.json'
        ))->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);

        $wrapper->processData();
        $this->assertEmpty($wrapper->getAttributes());
    }

    public function testChildCategoriesAreProperlyBuilt(): void
    {
        $categoryResponse = $this->getMockResponse('CategoryResponse/category_with_parent.json');
        $categories = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->exactly(3))->method('getCategory')
            ->withConsecutive([17], [16], [18])
            ->willReturnOnConsecutiveCalls(
                $categories->findOne(['id' => 17]),
                $categories->findOne(['id' => 16]),
                $categories->findOne(['id' => 18])
            );

        $variationEntity = $this->getVariationEntity('Pim/Variations/response_for_category_tree_test.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(2, $wrapper->getAttributes());
        // Attribute "cat".
        $this->assertSame('Living Room_Armchairs & Stools', $wrapper->getAttributes()[0]->getValues()[0]);
        // Attribute "cat_url".
        $this->assertSame('/living-room/armchairs-stools/', $wrapper->getAttributes()[1]->getValues()[0]);
    }

    public function testCategoryIsSkippedIfParentCategoryIsNotVisibleForExportingClient(): void
    {
        $categoryResponse = $this->getMockResponse('CategoryResponse/category_with_parent.json');
        $categories = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->exactly(3))->method('getCategory')
            ->withConsecutive([17], [16], [18])
            ->willReturnOnConsecutiveCalls(
                $categories->findOne(['id' => 17]),
                null,
                $categories->findOne(['id' => 18]),
            );

        $variationEntity = $this->getVariationEntity('Pim/Variations/response_for_category_tree_test.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(0, $wrapper->getAttributes());
    }

    public function testTagsAreProperlyProcessed(): void
    {
        $itemVariationResponse = $this->getMockResponse('Pim/Variations/variation_with_different_tag_clients.json');
        $variationEntities = PimVariationsParser::parse($itemVariationResponse);
        $variationEntity = $variationEntities->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(1, $wrapper->getAttributes());
        $this->assertSame(1, $wrapper->getAttributes()[0]->getValues()[0]);
        $this->assertCount(2, $wrapper->getTags());
        $this->assertSame('en-tag-1', $wrapper->getTags()[0]->getValue());
        $this->assertSame('en-tag-2', $wrapper->getTags()[1]->getValue());
    }

    public function characteristicsNotAvailableForSearchProvider(): array
    {
        $nonSearchable = $this->getMockResponse('ItemPropertyResponse/not_available_for_search.json');
        $nonSearchableProperties = ItemPropertyParser::parse($nonSearchable);

        $emptyNoGroup = $this->getMockResponse('ItemPropertyResponse/is_empty_without_group.json');
        $emptyNoGroupProperties = ItemPropertyParser::parse($emptyNoGroup);

        return [
            'is not searchable' => [
                'itemProperty' => $nonSearchableProperties->first(),
            ],
            'is empty and has no group' => [
                'itemProperty' => $emptyNoGroupProperties->first(),
            ],
        ];
    }

    /**
     * @dataProvider characteristicsNotAvailableForSearchProvider
     */
    public function testCharacteristicsNotAvailableForSearchAreIgnored(CharacteristicEntity $itemProperty): void
    {
        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json');

        $this->registryServiceMock->expects($this->once())->method('getItemProperty')
            ->willReturn($itemProperty);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );
        $wrapper->processData();

        $this->assertEmpty($wrapper->getAttributes());
    }

    public function testTaxRateIsTakenFromTheLastVariation(): void
    {
        $variationEntity = $this->getVariationEntity('Pim/Variations/variations_with_different_vat_ids.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->any())->method('getStandardVat')->willReturn($standardVat);

        $wrapper->processData();

        $this->assertEquals(7, $wrapper->getVatRate());
    }

    public function testTaxRateIsNotSetIfVariationUsesANonStandardVatId(): void
    {
        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_nonstandard_vat.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->any())->method('getStandardVat')->willReturn($standardVat);

        $wrapper->processData();
        $this->assertEquals(0, $wrapper->getVatRate());
    }

    public function skippedAttributesProvider(): array
    {
        return [
            'non-existing attributes' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/response_without_tags.json'),
                'attributeEntity' => null
            ],
            'empty attribute name' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_empty_attribute_value.json'),
                'attributeEntity' =>
                    AttributeParser::parse($this->getMockResponse('AttributeResponse/response.json'))->first()
            ]
        ];
    }

    /**
     * @dataProvider skippedAttributesProvider
     */
    public function testNonExportableAttributesAreSkipped(
        Variation $variationEntity,
        ?AttributeEntity $attributeEntity
    ): void {
        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getAttribute')
            ->willReturn($attributeEntity);

        $wrapper->processData();
        $this->assertEmpty($wrapper->getAttributes());
    }

    public function skippedPropertiesProvider(): array
    {
        return [
            'property not of type "item"' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_unknown_property_types.json'),
                'propertyEntity' => PropertyParser::parse($this->getMockResponse(
                    'PropertyResponse/property_with_unknown_type.json'
                ))->first(),
                false
            ],
            'property without translations for current language' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_without_translated_property.json'),
                'propertyEntity' =>
                    PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'))->first(),
                false
            ],
            'property of type selection without relation' => [
                'variationEntity' =>
                    $this->getVariationEntity(
                        'Pim/Variations/variation_with_selection_properties_without_relations.json'
                    ),
                'propertyEntity' =>
                    PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'))->first(),
                false
            ],
            'forced-skipped property' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_properties.json'),
                'propertyEntity' =>
                    PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'))->first(),
                true
            ]
        ];
    }

    /**
     * @dataProvider skippedPropertiesProvider
     */
    public function testNonExportablePropertiesAreSkipped(
        Variation $variationEntity,
        PropertyEntity $propertyEntity,
        bool $forceSkip
    ): void {
        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        if ($forceSkip) {
            $propertyEntity->setSkipExport(true);
        }
        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);

        $wrapper->processData();
        $this->assertEmpty($wrapper->getAttributes());
    }

    public function testPropertiesAreProperlyExported(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Number Property', ['8']),
            new Attribute('Float Property EN', ['100']),
            new Attribute('test-multiselect-property', ['value1']),
            new Attribute('no group (automatically generated)', ['Empty-cast Property EN'])
        ];

        $propertySelections = PropertySelectionParser::parse(
            $this->getMockResponse('PropertySelectionResponse/response.json')
        );
        $this->registryServiceMock->expects($this->any())->method('getPropertySelections')
            ->willReturn($propertySelections);

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_properties.json');
        $properties = PropertyParser::parse($this->getMockResponse('PropertyResponse/response.json'));
        $propertyGroups = PropertyGroupParser::parse($this->getMockResponse('PropertyGroupResponse/response.json'));

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity,
            $propertySelections
        );

        $this->registryServiceMock->expects($this->exactly(5))
            ->method('getProperty')
            ->withConsecutive([11], [7], [4], [5], [123])
            ->willReturnOnConsecutiveCalls(
                $properties->findOne(['id' => 11]),
                $properties->findOne(['id' => 7]),
                $properties->findOne(['id' => 4]),
                $properties->findOne(['id' => 5]),
                $properties->findOne(['id' => 123]),
            );

        $this->registryServiceMock->expects($this->exactly(2))
            ->method('getPropertyGroup')
            ->withConsecutive([555555], [2])
            ->willReturnOnConsecutiveCalls(
                null,
                $propertyGroups->findOne(['id' => 2])
            );

        $wrapper->processData();

        $this->assertCount(4, $wrapper->getAttributes());
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testTextPropertiesAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Shortie', ['I am a short text.']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_text_properties.json');
        $properties = PropertyParser::parse($this->getMockResponse('PropertyResponse/property_type_text.json'));
        $propertyEntity = $properties->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);

        $wrapper->processData();

        $this->assertCount(1, $wrapper->getAttributes());
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testSelectionPropertiesAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Color', ['Selection 1']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_selection_properties.json');
        $properties = PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'));
        $propertySelections = PropertySelectionParser::parse(
            $this->getMockResponse('PropertySelectionResponse/response.json')
        );
        $propertyEntity = $properties->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity,
            $propertySelections
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertySelections')
            ->willReturn($propertySelections);

        $wrapper->processData();

        $this->assertCount(1, $wrapper->getAttributes());
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testMultiSelectionPropertiesAreProperlyExported(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Shortie', ['value1', 'value 654654 en']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_multi_selection_properties.json');
        $properties = PropertyParser::parse(
            $this->getMockResponse('PropertyResponse/property_type_multi_selection.json')
        );
        $propertySelections = PropertySelectionParser::parse(
            $this->getMockResponse('PropertySelectionResponse/response.json')
        );
        $propertyEntity = $properties->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity,
            $propertySelections
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertySelections')
            ->willReturn($propertySelections);

        $wrapper->processData();

        $this->assertCount(1, $wrapper->getAttributes());
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testMultiSelectionPropertiesWithoutRelationsAreIgnored(): void
    {
        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_multi_selection_properties.json');
        $properties = PropertyParser::parse(
            $this->getMockResponse('PropertyResponse/property_type_multi_selection.json')
        );
        $propertyEntity = $properties->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertySelections')->willReturn(null);

        $wrapper->processData();

        $this->assertEmpty($wrapper->getAttributes());
    }

    public function nonExportableCharacteristicsProvider(): array
    {
        return [
            'characteristic not available in registry' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' => null,
                'propertyGroupEntity' => null,
            ],
            'characteristic not available for search' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/not_available_for_search.json')
                    )->first(),
                'propertyGroupEntity' => null,
            ],
            'characteristic cast type is empty and without group' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_empty_without_group.json')
                    )->first(),
                'propertyGroupEntity' => null,
            ],
            'characteristic cast type is empty and property group name is empty' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_empty_with_group.json')
                    )->first(),
                'propertyGroupEntity' =>
                    ItemPropertyGroupParser::parse(
                        $this->getMockResponse('ItemPropertyGroupResponse/empty_name.json')
                    )->first(),
            ],
            'characteristic cast type is empty and property group is not available in registry' => [
                'variationEntity' => $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_empty_with_group.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
            'characteristic cast type is text but translation is empty' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_empty_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_text.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
            'characteristic cast type is text but item property translation is empty' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_text_and_empty.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
            'characteristic cast type is text but characteristic text has no translation' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_without_translated_characteristics.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_text.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
            'characteristic cast type is selection but characteristic value has no translation' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/without_translated_characteristic_selection.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_selection.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
            'characteristic unknown cast type' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_unknown_characteristic_type.json'),
                'characteristicEntity' =>
                    ItemPropertyParser::parse(
                        $this->getMockResponse('ItemPropertyResponse/is_unknown.json')
                    )->first(),
                'propertyGroupEntity' => null
            ],
        ];
    }

    /**
     * @dataProvider nonExportableCharacteristicsProvider
     */
    public function testNonExportableCharacteristicsAreSkipped(
        Variation $variationEntity,
        ?CharacteristicEntity $characteristicEntity,
        ?ItemPropertyGroup $propertyGroupEntity
    ): void {
        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getItemPropertyGroup')
            ->willReturn($propertyGroupEntity);

        $wrapper->processData();
        $this->assertEmpty($wrapper->getAttributes());
    }

    public function testEmptyCharacteristicIsExportedWithPropertyGroupName(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Mein Paket', ['Test']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/with_group.json')
        )->first();
        $propertyGroupEntity = ItemPropertyGroupParser::parse(
            $this->getMockResponse('ItemPropertyGroupResponse/one.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getItemPropertyGroup')
            ->willReturn($propertyGroupEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    /**
     * @dataProvider emptyCharacteristicIsExportedWithTranslatedNameTestProvider
     */
    public function testEmptyCharacteristicIsExportedWithTranslatedName(
        string $language,
        array $expectedAttributes
    ): void {
        $config = $this->getDefaultConfig();
        $config->setLanguage($language);

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/response_variation_with_translatable_characteristics.json'
        );

        $wrapper = new VariationWrapper(
            $config,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/translatable_item_properties.json')
        )->first();
        $propertyGroupEntity = ItemPropertyGroupParser::parse(
            $this->getMockResponse('ItemPropertyGroupResponse/empty_with_names.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getItemPropertyGroup')
            ->willReturn($propertyGroupEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedAttributes, $wrapper->getAttributes());
    }

    public function emptyCharacteristicIsExportedWithTranslatedNameTestProvider(): array
    {
        return [
            'using EN language' => [
                'en',
                [new Attribute('Test Group EN', ['Third Property EN'])]
            ],
            'using DE language' => [
                'de',
                [new Attribute('Test Group DE', ['third test de'])]
            ],
        ];
    }

    public function testTextCharacteristicsAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Chain length', ['Length <40 cm']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_characteristic.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/is_text.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testSelectionCharacteristicsAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Chain length', ['Length <40 cm']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_characteristic_selections.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/is_selection.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testFloatCharacteristicsAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Float characteristic', ['4.13']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_float_characteristic.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/is_float.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testIntCharacteristicsAreExportedProperly(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Int characteristic', ['1337']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_int_characteristic.json');

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/is_int.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testUsesCharacteristicGroupAsFallbackIfNoProperValueCanBeFound(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Mein Paket', ['Test']),
        ];

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variation_without_translated_characteristics.json'
        );

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $characteristicEntity = ItemPropertyParser::parse(
            $this->getMockResponse('ItemPropertyResponse/text_with_group.json')
        )->first();
        $propertyGroupEntity = ItemPropertyGroupParser::parse(
            $this->getMockResponse('ItemPropertyGroupResponse/one.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getItemPropertyGroup')
            ->willReturn($propertyGroupEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testDimensionsAreExportedAsAttributes(): void
    {
        $expectedExportedAttributes = [
            new Attribute('dimensions_height_mm', ['300']),
            new Attribute('dimensions_length_mm', ['200']),
            new Attribute('dimensions_width_mm', ['100']),
            new Attribute('dimensions_weight_g', ['2000']),
            new Attribute('dimensions_weight_net_g', ['1000']),
        ];

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variation_with_dimensions.json'
        );

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testDimensionsAreExportedWithConfiguredUnit(): void
    {
        $expectedExportedAttributes = [
            new Attribute('dimensions_height_m', ['0.3']),
            new Attribute('dimensions_length_m', ['0.2']),
            new Attribute('dimensions_width_m', ['0.1']),
            new Attribute('dimensions_weight_kg', ['2']),
            new Attribute('dimensions_weight_net_kg', ['1']),
        ];

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variation_with_dimensions.json'
        );

        $config = clone $this->defaultConfig;
        $config->setExportDimensionUnit('m');
        $config->setExportWeightUnit('kg');

        $wrapper = new VariationWrapper(
            $config,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    public function testDimensionsWithoutValueAreIgnored(): void
    {
        $expectedExportedAttributes = [
            new Attribute('dimensions_height_mm', ['300']),
            new Attribute('dimensions_length_mm', ['200']),
            new Attribute('dimensions_width_mm', ['100']),
            new Attribute('dimensions_weight_g', ['2000']),
            new Attribute('dimensions_weight_net_g', ['1000']),
        ];

        $variationEntity = $this->getVariationEntity(
            'Pim/Variations/variants_with_dimensions.json'
        );

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
    }

    private function getVariationEntity(string $responsePath): Variation
    {
        $itemVariationResponse = $this->getMockResponse($responsePath);
        $variationEntities = PimVariationsParser::parse($itemVariationResponse);

        return $variationEntities->first();
    }
}
