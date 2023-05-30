<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use Log4Php\Logger;
use FINDOLOGIC\Export\Exporter;
use PHPUnit\Framework\TestCase;
use FINDOLOGIC\Export\CSV\CSVItem;
use FINDOLOGIC\Export\CSV\CSVConfig;
use FINDOLOGIC\Export\Data\Attribute;
use PHPUnit\Framework\MockObject\MockObject;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\PlentyShop;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\ItemsWrapper;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ItemHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as WebStoreConfiguration;

class CsvWrapperTest extends TestCase
{
    use ConfigHelper;

    use ResponseHelper;

    use ItemHelper;

    private const TEST_EXPORT_PATH = 'some_path';

    private ItemsWrapper $csvWrapper;

    private Config $config;

    private Exporter|MockObject $exporterMock;

    private RegistryService|MockObject $registryServiceMock;

    private Logger|MockObject $loggerMock;

    private CSVConfig $csvConfig;

    public function setUp(): void
    {
        $this->exporterMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getDefaultConfig();
        $this->config->setLanguage('en');

        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $categoryResponse = $this->getMockResponse('CategoryResponse/one.json');
        $parsedCategoryResponse = CategoryParser::parse($categoryResponse);
        $this->registryServiceMock->method('getCategory')->willReturn($parsedCategoryResponse->first());

        $webstoreConfigMock = $this->getMockBuilder(WebStoreConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webstoreConfigMock->method('getDisplayItemName')->willReturn(1);
        $webstoreConfigMock->method('getDefaultLanguage')->willReturn('en');
        $webstoreConfigMock->method('getDomainSsl')->willReturn('https://plenty-testshop.de');

        $webstoreMock = $this->getMockBuilder(WebStore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webstoreMock->method('getConfiguration')->willReturn($webstoreConfigMock);
        $webstoreMock->method('getStoreIdentifier')->willReturn(12345);

        $this->registryServiceMock->method('getWebStore')->willReturn($webstoreMock);

        $this->csvWrapper = new ItemsWrapper(
            self::TEST_EXPORT_PATH,
            null,
            $this->exporterMock,
            $this->config,
            $this->registryServiceMock,
            $this->loggerMock,
            $this->loggerMock
        );

        $this->csvConfig = new CSVConfig([
            'price_id',
            'variation_id',
            'base_unit',
            'package_size'
        ], [
            'cat'
        ]);
    }

    public function testExportsEachVariationSeparatelyIfConfiguredAndIfAllVariationsHaveAGroupableAttribute()
    {
        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->exporterMock->expects($this->exactly(4))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem('106'),
            new CSVItem('106'),
            new CSVItem('106'),
            new CSVItem('106')
        );

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/three_variations_for_one_item_with_all_having_groupable_attribute_values.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')->with(3)->willReturn($attributes->findOne(['id' => 3]));

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    /** @var CSVItem[] $items */
                    $this->assertCount(3, $items);

                    $expectedIds = [
                        '106_1004',
                        '106_1005',
                        '106_1006'
                    ];

                    $expectedOrderNumbers = [
                        ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213'],
                        ['101', '1005', '106'],
                        ['102', '1006', '106']
                    ];

                    $expectedUrls = [
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1004',
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1005',
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1006',
                    ];

                    $expectedAttributes = [
                        [
                            'cat' => 'Armchairs & Stools',
                            'cat_url' => '/wohnzimmer/sessel-hocker/',
                            'groupable attribute en' => 'purple',
                            'free7' => '0',
                            'free8' => '0',
                            'free9' => '0',
                            'free10' => '0',
                            'free11' => '0',
                            'free12' => '0',
                            'free13' => '0',
                            'free14' => '0',
                            'free15' => '0',
                            'free16' => '0',
                            'free17' => '0',
                            'free18' => '0',
                            'free19' => '0',
                            'free20' => '0'
                        ]
                    ];

                    $expectedAttributes[] = [...$expectedAttributes[0], 'groupable attribute en' => 'black'];
                    $expectedAttributes[] = [...$expectedAttributes[0], 'groupable attribute en' => 'white'];

                    foreach ($items as $key => $item) {
                        $orderNumbers = $this->getOrderNumbers($item);

                        $this->assertEquals($expectedIds[$key], $item->getId());
                        $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);

                        $url = $item->getUrl()->getValues();
                        $this->assertEquals($expectedUrls[$key], reset($url));

                        $attributesMap = $this->getMappedAttributes($item);

                        foreach ($expectedAttributes[$key] as $attributeKey => $expectedAttributeValue) {
                            $this->assertEquals($expectedAttributeValue, $attributesMap[$attributeKey][0]);
                        }
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testExportsThreeItemsWhenTwoOutOfThreeVariantsHaveGroupableAttributes()
    {
        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->exporterMock->expects($this->exactly(3))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem('106'),
            new CSVItem('106'),
            new CSVItem('106')
        );

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/three_variations_for_one_item_with_two_having_groupable_attribute_values.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([3], [1], [3])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3])
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(3, $items);

                    $expectedIds = [
                        '106_1004',
                        '106_1006',
                        '106'
                    ];

                    $expectedOrderNumbers = [
                        ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213'],
                        ['102', '1006', '106'],
                        ['101', '1005', '106'],
                    ];

                    foreach ($items as $key => $item) {
                        $this->assertEquals($expectedIds[$key], $item->getId());

                        $orderNumbers = $this->getOrderNumbers($item);

                        $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testExportsTwoItemsWhenOnlyOneOutOfThreeVariantsHasGroupableAttributes()
    {
        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->exporterMock->expects($this->exactly(2))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem('106'),
            new CSVItem('106')
        );

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/three_variations_for_one_item_with_one_having_groupable_attribute_values.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([1], [1], [3])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3])
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(2, $items);

                    $expectedIds = [
                        '106_1006',
                        '106'
                    ];

                    $expectedOrderNumbers = [
                        ['102', '1006', '106'],
                        ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213', '101', '1005']
                    ];

                    foreach ($items as $key => $item) {
                        $orderNumbers = $this->getOrderNumbers($item);

                        $this->assertEquals($expectedIds[$key], $item->getId());
                        $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testGroupsVariantsWithGroupableAttributesIntoASingleItemIfConfiguredNotToShowSeparately()
    {
        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'combined'
                ]
            );

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem('106'));

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/three_variations_for_one_item_with_all_having_groupable_attribute_values.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([3], [3], [3])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 3])
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(1, $items);

                    $expectedId = '106';
                    $expectedOrderNumber = ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213', '101', '1005', '102', '1006'];

                    $orderNumbers = $this->getOrderNumbers($items[0]);
                    $this->assertEquals($expectedId, $items[0]->getId());
                    $this->assertEquals($expectedOrderNumber, $orderNumbers);

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testGroupsVariantsWithoutGroupableAttributesIntoASingleItemDespiteConfigurationToShowSeparately()
    {
        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem('106'));

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/three_variations_for_one_item_with_none_having_groupable_attribute_values.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([1], [1], [1])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 1])
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(1, $items);

                    $expectedId = '106';
                    $expectedOrderNumber = ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213', '101', '1005', '102', '1006'];

                    $orderNumbers = $this->getOrderNumbers($items[0]);
                    $this->assertEquals($expectedId, $items[0]->getId());
                    $this->assertEquals($expectedOrderNumber, $orderNumbers);

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testProductsWithSingleVariationAndGroupableAttributeIsExportedTheSameWayRegardlessOfConfiguration()
    {
        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')->with(3)->willReturn($attributes->findOne(['id' => 3]));

        $exporterMockCopy = clone $this->exporterMock;
        $registryMockCopy = clone $this->registryServiceMock;
        $anotherCsvWrapper = new ItemsWrapper(
            self::TEST_EXPORT_PATH,
            null,
            $exporterMockCopy,
            $this->config,
            $registryMockCopy,
            $this->loggerMock,
            $this->loggerMock
        );

        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse('Pim/Variations/variation_with_groupable_attribute.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $firstItemData = null;

        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);
        $this->exporterMock->expects($this->exactly(2))
            ->method('createItem')
            ->willReturnOnConsecutiveCalls(
                new CSVItem('106'),
                new CSVItem('106')
            );
        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $firstItemData = $items[0]->getCsvFragment($this->csvConfig);

                    return true;
                })
            );
        $this->csvWrapper->wrap(0, 1, $items, $variations);

        $registryMockCopy->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'notAll'
                ]
            );
        $exporterMockCopy->expects($this->once())
            ->method('createItem')
            ->willReturn(new CSVItem('106'));
        $exporterMockCopy->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $expectedSeparatedProductId = '106_1004';
                    $expectedGroupedProductId = '106';

                    $this->assertCount(1, $items);

                    $separatedProductColumns = explode("\t", $firstItemData);
                    $groupedProductColumns = explode("\t", $items[0]->getCsvFragment($this->csvConfig));

                    $this->assertEquals($expectedSeparatedProductId, $separatedProductColumns[0]);
                    $this->assertEquals($expectedGroupedProductId, $groupedProductColumns[0]);
                    $this->assertEquals(
                        array_slice($separatedProductColumns, 1),
                        array_slice($groupedProductColumns, 1)
                    );

                    return true;
                })
            );
        $anotherCsvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testProductsWithSingleVariationGetExportedTheSameWhetherItHasGroupableAttributesOrNot()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse('Pim/Variations/variation_with_groupable_attribute.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $exporterMockCopy = clone $this->exporterMock;
        $registryMockCopy = clone $this->registryServiceMock;
        $anotherCsvWrapper = new ItemsWrapper(
            self::TEST_EXPORT_PATH,
            null,
            $exporterMockCopy,
            $this->config,
            $registryMockCopy,
            $this->loggerMock,
            $this->loggerMock
        );

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')->with(3)->willReturn($attributes->findOne(['id' => 3]));

        $modifiedAttributeResponse = $this->getMockResponse(
            'AttributeResponse/response_where_attribute_with_id_3_is_not_groupable.json'
        );
        $modifiedAttributes = AttributeParser::parse($modifiedAttributeResponse);
        $registryMockCopy->method('getAttribute')->with(3)->willReturn($modifiedAttributes->findOne(['id' => 3]));

        $firstItemData = null;
        $this->exporterMock->expects($this->exactly(2))
            ->method('createItem')
            ->willReturnOnConsecutiveCalls(
                new CSVItem('106'),
                new CSVItem('106')
            );
        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $firstItemData = $items[0]->getCsvFragment($this->csvConfig);

                    return true;
                })
            );
        $this->csvWrapper->wrap(0, 1, $items, $variations);

        $exporterMockCopy->expects($this->once())
            ->method('createItem')
            ->willReturn(new CSVItem('106'));
        $exporterMockCopy->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $expectedSeparatedProductId = '106_1004';
                    $expectedGroupedProductId = '106';

                    $this->assertCount(1, $items);

                    $separatedProductColumns = explode("\t", $firstItemData);
                    $groupedProductColumns = explode("\t", $items[0]->getCsvFragment($this->csvConfig));

                    $this->assertEquals($expectedSeparatedProductId, $separatedProductColumns[0]);
                    $this->assertEquals($expectedGroupedProductId, $groupedProductColumns[0]);
                    $this->assertEquals(
                        array_slice($separatedProductColumns, 1),
                        array_slice($groupedProductColumns, 1)
                    );

                    return true;
                })
            );
        $anotherCsvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testProductsWithMainVariationIncludingAnExportExclusionTagAreSkippedAndMessageIsLogged()
    {
        $itemFirstPageResponse = $this->getMockResponse(
            'ItemResponse/response_with_three_items_for_exclusion_tag_test_page1.json'
        );
        $items1 = ItemParser::parse($itemFirstPageResponse);

        $itemSecondPageResponse = $this->getMockResponse(
            'ItemResponse/response_with_three_items_for_exclusion_tag_test_page2.json'
        );
        $items2 = ItemParser::parse($itemSecondPageResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/variations_for_six_items_where_main_variation_of_four_has_exclusion_tag.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                ['Products with id 106, 108 were skipped, as they contain the tag "findologic-exclude"'],
                ['Products with id 109, 111 were skipped, as they contain the tag "findologic-exclude"']
            );
        $this->exporterMock->expects($this->exactly(2))->method('createItem');

        $this->csvWrapper->wrap(0, 1, $items1, $variations);
        $this->csvWrapper->wrap(0, 2, $items2, $variations);
    }

    public function testLoggingIsSkippedIfSkipableItemsDoNotExist(): void
    {
        $itemFirstPageResponse = $this->getMockResponse(
            'ItemResponse/response_with_three_items_for_exclusion_tag_test_page1.json'
        );

        $items = ItemParser::parse($itemFirstPageResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/response_for_six_items_where_main_variation_have_no_exclusion_tag.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $this->loggerMock->expects($this->never())->method('notice');

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testFailureLogGroupingByReason()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $items = ItemParser::parse($itemResponse);

        $differentItemResponse = $this->getMockResponse('ItemResponse/response_with_different_ids.json');
        $differentItems = ItemParser::parse($differentItemResponse);

        $variationResponse = $this->getMockResponse('Pim/Variations/empty_response.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $this->loggerMock->expects($this->exactly(2))
            ->method('warning')
            ->withConsecutive(
                [
                    'Products with id 102, 103, 104, 105, 106, 107 could not be exported. ' .
                        'Reason: Product has no variations.'
                ],
                [
                    'Products with id 108, 109, 110, 111, 112, 113 could not be exported. ' .
                        'Reason: Product has no variations.'
                ]
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
        $this->csvWrapper->wrap(0, 1, $differentItems, $variations);
    }

    public function testNonMainVariationsWithExclusionTagAreSkipped()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/variations_for_one_item_where_all_non_mains_have_exclusion_tag.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem('106'));

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(1, $items);

                    // No 1006
                    $expectedOrderNumbers = ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213'];

                    $orderNumbers = $this->getOrderNumbers($items[0]);
                    $this->assertEquals($expectedOrderNumbers, $orderNumbers);

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testProductsWithExclusionTagsAreExportedIfExlusionTagDoesNotHaveATranslateionInCurrentLanguage()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/one.json');
        $items = ItemParser::parse($itemResponse);

        $this->config->setLanguage('en');

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/variations_for_one_item_where_main_has_exclusion_tag_only_for_de_lang.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem('106'));

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(1, $items);

                    // No 1006
                    $expectedOrderNumbers = ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213', '101', '1005'];

                    $orderNumbers = $this->getOrderNumbers($items[0]);
                    $this->assertEquals($expectedOrderNumbers, $orderNumbers);

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testSeparatedVariationsIsGroupedBasedOnTwoGroupableAttributes(): void
    {
        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->exporterMock->expects($this->exactly(5))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem('108'),
            new CSVItem('108'),
            new CSVItem('108'),
            new CSVItem('108'),
            new CSVItem('108')
        );

        $itemResponse = $this->getMockResponse(
            'ItemResponse/response_for_items_separation_with_two_attributes_test.json'
        );
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/response_with_two_groupable_and_one_not_attributes_test.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response_for_separating_items.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([3], [4], [1], [1], [4], [3], [3], [4], [1], [3], [4], [1], [3], [4], [1])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 4]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 4]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 4]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 4]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 4]),
                $attributes->findOne(['id' => 1]),
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(4, $items);

                    $expectedIds = [
                        '108_1152',
                        '108_1160',
                        '108_1167',
                        '108_1168'
                    ];

                    $expectedOrderNumbers = [
                        ['yellow-yes-xl', '1152', '108'],
                        ['green-yes-xs', '1160', '108'],
                        ['yellow-no-l', '1167', '108'],
                        ['orange-no-l', '1168', '108', 'orange-no-m', '1171']
                    ];

                    $expectedImagesUrls = [
                        'https://cdn03.plentymarkets.com/item/images/108/middle/108-Barsessel-Black-Mamba-1.jpg',
                        'https://cdn03.plentymarkets.com/item/images/108/middle/108-Barsessel-Black-Mamba-2.jpg',
                        'https://cdn03.plentymarkets.com/item/images/108/middle/108-Barsessel-Black-Mamba-3.jpg',
                        'https://cdn03.plentymarkets.com/item/images/108/middle/108-Barsessel-Black-Mamba-4.jpg'
                    ];

                    foreach ($items as $key => $item) {
                        $orderNumbers = $this->getOrderNumbers($item);
                        $itemImages = $this->getImages($item);

                        $this->assertEquals($expectedIds[$key], $item->getId());
                        $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);
                        $this->assertSame($expectedImagesUrls[$key], $itemImages[0]->getUrl());
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testSeparatedVariationsIsGroupedBasedOnOneGroupableAttribute(): void
    {
        $plentyShop = new PlentyShop([
            PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
            PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'all'
        ]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->exporterMock->expects($this->exactly(4))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem('133'),
            new CSVItem('133'),
            new CSVItem('133'),
            new CSVItem('133'),
        );

        $itemResponse = $this->getMockResponse(
            'ItemResponse/response_for_items_separation_with_one_group_attribute_test.json'
        );
        $items = ItemParser::parse($itemResponse);

        $variationResponse = $this->getMockResponse(
            'Pim/Variations/response_with_one_groupable_and_one_not_attributes.json'
        );
        $variations = PimVariationsParser::parse($variationResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response_for_separating_items.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $this->registryServiceMock->method('getAttribute')
            ->withConsecutive([3], [1], [3], [1], [3], [1], [3], [1], [3], [1], [1], [3])
            ->willReturnOnConsecutiveCalls(
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 1]),
                $attributes->findOne(['id' => 3]),
            );

        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) {
                    $this->assertCount(3, $items);

                    $expectedIds = [
                        '133_1118',
                        '133_1119',
                        '133_1126'
                    ];

                    $expectedOrderNumbers = [
                        ['133-green-xl', '1118', '133', '133-green-l', '1121'],
                        ['133-blue-xl', '1119', '133', '133-blue-l', '1122', '133-blue-s', '1125'],
                        ['133-black-s', '1126', '133']
                    ];

                    foreach ($items as $key => $item) {
                        $orderNumbers = $this->getOrderNumbers($item);
                        $this->assertEquals($expectedIds[$key], $item->getId());
                        $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }
}
