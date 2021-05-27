<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute as AttributeEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty as CharacteristicEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property as PropertyEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Variation as VariationWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{
    use ResponseHelper;
    use ConfigHelper;

    /** @var Registry|MockObject */
    private $registryMock;

    /** @var Config */
    private $defaultConfig;

    /** @var RegistryService|MockObject */
    private $registryServiceMock;

    public function setUp(): void
    {
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();

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

    public function testChildCategoriesAreProperlyBuilt(): void
    {
        $categoryResponse = $this->getMockResponse('CategoryResponse/category_with_parent.json');
        $categories = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->exactly(2))->method('getCategory')
            ->willReturnOnConsecutiveCalls(
                $categories->findOne(['hasChildren' => false]),
                $categories->findOne(['hasChildren' => true]),
            );

        $variationEntity = $this->getVariationEntity('Pim/Variations/response.json');

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

    public function testTagsAreProperlyProcessed(): void
    {
        $webStoreMock = $this->getMockBuilder(WebStore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webStoreMock->method('getStoreIdentifier')->willReturn(34185);
        $this->registryServiceMock->method('getWebStore')->willReturn($webStoreMock);

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
        $this->assertSame('1', $wrapper->getAttributes()[0]->getValues()[0]);
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

        $this->assertEquals($wrapper->getVatRate(), 7);
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
                ))->first()
            ],
            'property with cast empty' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_with_property_of_empty_cast_type.json'),
                'propertyEntity' =>
                    PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'))->first()
            ],
            'property without translations for current language' => [
                'variationEntity' =>
                    $this->getVariationEntity('Pim/Variations/variation_without_translated_property.json'),
                'propertyEntity' =>
                    PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'))->first()
            ]
        ];
    }

    /**
     * @dataProvider skippedPropertiesProvider
     */
    public function testNonExportablePropertiesAreSkipped(
        Variation $variationEntity,
        PropertyEntity $propertyEntity
    ): void {
        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);

        $wrapper->processData();
        $this->assertEmpty($wrapper->getAttributes());
    }

    public function testPropertiesAreProperlyExported(): void
    {
        $expectedExportedAttributes = [
            new Attribute('Color', ['8']),
            new Attribute('Color', ['100']),
        ];

        $variationEntity = $this->getVariationEntity('Pim/Variations/variation_with_properties.json');
        $properties = PropertyParser::parse($this->getMockResponse('PropertyResponse/one.json'));
        $propertyEntity = $properties->first();

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getProperty')->willReturn($propertyEntity);

        $wrapper->processData();

        $this->assertCount(2, $wrapper->getAttributes());
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
            $variationEntity
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
                    PropertyGroupParser::parse(
                        $this->getMockResponse('PropertyGroupResponse/empty_name.json')
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
        ?PropertyGroup $propertyGroupEntity
    ): void {
        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryServiceMock,
            $variationEntity
        );

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertyGroup')
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
        $propertyGroupEntity = PropertyGroupParser::parse(
            $this->getMockResponse('PropertyGroupResponse/one.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertyGroup')
            ->willReturn($propertyGroupEntity);

        $wrapper->processData();
        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $wrapper->getAttributes());
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
        $propertyGroupEntity = PropertyGroupParser::parse(
            $this->getMockResponse('PropertyGroupResponse/one.json')
        )->first();

        $this->registryServiceMock->expects($this->any())->method('getItemProperty')
            ->willReturn($characteristicEntity);
        $this->registryServiceMock->expects($this->any())->method('getPropertyGroup')
            ->willReturn($propertyGroupEntity);

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
