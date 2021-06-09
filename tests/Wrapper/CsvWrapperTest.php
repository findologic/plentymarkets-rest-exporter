<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\Export\CSV\CSVItem;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as WebStoreConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\CsvWrapper;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CsvWrapperTest extends TestCase
{
    use ConfigHelper;

    use ResponseHelper;

    private const TEST_EXPORT_PATH = 'some_path';

    /**
     * @var Exporter|MockObject
     */
    private $exporterMock;

    /**
     * @var RegistryService|MockObject
     */
    private $registryServiceMock;

    /**
     * @var CsvWrapper
     */
    private $csvWrapper;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

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

        $webstoreMock = $this->getMockBuilder(WebStore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webstoreMock->method('getConfiguration')->willReturn($webstoreConfigMock);

        $this->registryServiceMock->method('getWebStore')->willReturn($webstoreMock);

        $this->csvWrapper = new CsvWrapper(
            self::TEST_EXPORT_PATH,
            null,
            $this->exporterMock,
            $this->config,
            $this->registryServiceMock,
            $this->loggerMock,
            $this->loggerMock
        );
    }

    public function testExportsEachVariationSeparatelyIfConfiguredAndIfAllVariationsHaveAGroupableAttribute()
    {
        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );

        $this->exporterMock->expects($this->exactly(4))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem(106),
            new CSVItem(106),
            new CSVItem(106),
            new CSVItem(106)
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

                    $expectedIdentifiers = [
                        'S-000813-C|modeeeel|1004|106|3213213213213',
                        '101|1005|106',
                        '102|1006|106'
                    ];

                    $expectedUrls = [
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1004',
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1005',
                        'https://plenty-testshop.de/modern-office-chair-merrick-green_106_1006',
                    ];

                    $expectedAttributes = [
                        'cat=Armchairs+%26+Stools&cat_url=%2Fwohnzimmer%2Fsessel-hocker%2F&groupable+attribute+en=' .
                        'purple&free7=0&free8=0&free9=0&free10=0&free11=0&free12=0&free13=0&free14=0&free15=0&free16=' .
                        '0&free17=0&free18=0&free19=0&free20=0',
                        'cat=Armchairs+%26+Stools&cat_url=%2Fwohnzimmer%2Fsessel-hocker%2F&groupable+attribute+en=' .
                        'black&free7=0&free8=0&free9=0&free10=0&free11=0&free12=0&free13=0&free14=0&free15=0&free16=' .
                        '0&free17=0&free18=0&free19=0&free20=0',
                        'cat=Armchairs+%26+Stools&cat_url=%2Fwohnzimmer%2Fsessel-hocker%2F&groupable+attribute+en=' .
                        'white&free7=0&free8=0&free9=0&free10=0&free11=0&free12=0&free13=0&free14=0&free15=0&free16=' .
                        '0&free17=0&free18=0&free19=0&free20=0'
                    ];

                    foreach ($items as $key => $item) {
                        $line = $item->getCsvFragment();
                        $columnValues = explode("\t", $line);
                        $this->assertEquals($expectedIdentifiers[$key], $columnValues[1]);

                        $url = $item->getUrl()->getValues();
                        $this->assertEquals($expectedUrls[$key], reset($url));

                        $this->assertEquals($expectedAttributes[$key], $columnValues[11]);
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testExportsThreeItemsWhenTwoOutOfThreeVariantsHaveGroupableAttributes()
    {
        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );

        $this->exporterMock->expects($this->exactly(3))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem(106),
            new CSVItem(106),
            new CSVItem(106)
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

                    $expectedIdentifiers = [
                        'S-000813-C|modeeeel|1004|106|3213213213213',
                        '102|1006|106',
                        '101|1005|106'
                    ];

                    foreach ($items as $key => $item) {
                        $line = $item->getCsvFragment();
                        $columnValues = explode("\t", $line);
                        $this->assertEquals($expectedIdentifiers[$key], $columnValues[1]);
                    }

                    return true;
                })
            );

        $this->csvWrapper->wrap(0, 1, $items, $variations);
    }

    public function testExportsTwoItemsWhenOnlyOneOutOfThreeVariantsHasGroupableAttributes()
    {
        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );

        $this->exporterMock->expects($this->exactly(2))->method('createItem')->willReturnOnConsecutiveCalls(
            new CSVItem(106),
            new CSVItem(106)
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

                    $expectedIdentifiers = [
                        '102|1006|106',
                        'S-000813-C|modeeeel|1004|106|3213213213213|101|1005'
                    ];

                    foreach ($items as $key => $item) {
                        $line = $item->getCsvFragment();
                        $columnValues = explode("\t", $line);
                        $this->assertEquals($expectedIdentifiers[$key], $columnValues[1]);
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

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem(106));

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

                    $expectedIdentifier = 'S-000813-C|modeeeel|1004|106|3213213213213|101|1005|102|1006';

                    $line = $items[0]->getCsvFragment();
                    $columnValues = explode("\t", $line);
                    $this->assertEquals($expectedIdentifier, $columnValues[1]);

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

        $this->exporterMock->expects($this->once())->method('createItem')->willReturn(new CSVItem(106));

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

                    $expectedIdentifier = 'S-000813-C|modeeeel|1004|106|3213213213213|101|1005|102|1006';

                    $line = $items[0]->getCsvFragment();
                    $columnValues = explode("\t", $line);
                    $this->assertEquals($expectedIdentifier, $columnValues[1]);

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
        $anotherCsvWrapper = new CsvWrapper(
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

        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );
        $this->exporterMock->expects($this->exactly(2))
            ->method('createItem')
            ->willReturnOnConsecutiveCalls(
                new CSVItem(106),
                new CSVItem(106)
            );
        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $firstItemData = $items[0]->getCsvFragment();

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
            ->willReturn(new CSVItem(106));
        $exporterMockCopy->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $this->assertEquals($firstItemData, $items[0]->getCsvFragment());

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

        $this->registryServiceMock->method('getPluginConfigurations')
            ->with('Ceres')
            ->willReturn(
                [
                    'global.enableOldUrlPattern' => false,
                    'item.variation_show_type' => 'all'
                ]
            );

        $exporterMockCopy = clone $this->exporterMock;
        $registryMockCopy = clone $this->registryServiceMock;
        $anotherCsvWrapper = new CsvWrapper(
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
                new CSVItem(106),
                new CSVItem(106)
            );
        $this->exporterMock->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $firstItemData = $items[0]->getCsvFragment();

                    return true;
                })
            );
        $this->csvWrapper->wrap(0, 1, $items, $variations);

        $exporterMockCopy->expects($this->once())
            ->method('createItem')
            ->willReturn(new CSVItem(106));
        $exporterMockCopy->expects($this->once())
            ->method('serializeItemsToFile')
            ->with(
                self::TEST_EXPORT_PATH,
                $this->callback(function (array $items) use (&$firstItemData) {
                    $this->assertCount(1, $items);

                    $this->assertEquals($firstItemData, $items[0]->getCsvFragment());

                    return true;
                })
            );
        $anotherCsvWrapper->wrap(0, 1, $items, $variations);
    }
}
