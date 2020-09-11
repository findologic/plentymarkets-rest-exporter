<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use FINDOLOGIC\Export\CSV\CSVExporter as CsvFileExporter;
use FINDOLOGIC\Export\CSV\CSVItem;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\Export\Data\Usergroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExporterTest extends TestCase
{
    use ResponseHelper;
    use ConfigHelper;

    /** @var Config */
    private $defaultConfig;

    /** @var Logger|MockObject */
    private $loggerMock;

    /** @var Client|MockObject */
    private $clientMock;

    /** @var Registry|MockObject */
    private $registryMock;

    /** @var RegistryService|MockObject */
    private $registryServiceMock;

    /** @var ItemRequest|MockObject */
    private $itemRequestMock;

    /** @var ItemVariationRequest|MockObject */
    private $itemVariationRequestMock;

    /** @var CsvFileExporter|MockObject  */
    private $fileExporterMock;

    private function getDefaultExporter(int $type): Exporter
    {
        return Exporter::buildInstance(
            $type,
            $this->defaultConfig,
            $this->loggerMock,
            $this->loggerMock,
            $this->clientMock,
            $this->registryMock,
            $this->registryServiceMock,
            $this->itemRequestMock,
            $this->itemVariationRequestMock,
            $this->fileExporterMock,
            'exportpath'
        );
    }

    public function setUp(): void
    {
        $this->defaultConfig = $this->getDefaultConfig();
        $this->defaultConfig->setLanguage('en');
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['warmUp'])
            ->getMock();
        $this->itemRequestMock = $this->getMockBuilder(ItemRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemVariationRequestMock = $this->getMockBuilder(ItemVariationRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemVariationRequestMock->method('setPage')->willReturnSelf();
        $this->itemVariationRequestMock->method('setItemId')->willReturnSelf();
        $this->itemVariationRequestMock->method('setWith')->willReturnSelf();
        $this->itemVariationRequestMock->method('setIsActive')->willReturnSelf();
        $this->fileExporterMock = $this->getMockBuilder(CsvFileExporter::class)
            ->setMethods(['serializeItemsToFile'])
            ->setConstructorArgs([100, []])
            ->getMock();
    }

    public function exporterTypeProvider(): array
    {
        return [
            'Exporter type is CSV' => [
                'type' => Exporter::TYPE_CSV,
                'expected' => CsvExporter::class
            ],
            'Exporter type is XML' => [
                'type' => Exporter::TYPE_XML,
                'expected' => XmlExporter::class
            ],
        ];
    }

    /**
     * @dataProvider exporterTypeProvider
     * @param int $type
     * @param string $expected
     */
    public function testExporterReturnsCorrectType(int $type, string $expected): void
    {
        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['defaultLanguage' => 'en']
        ]);

        $this->registryMock->method('get')->willReturn($webStore);

        $exporter = Exporter::buildInstance(
            $type,
            $this->defaultConfig,
            $this->loggerMock,
            $this->loggerMock,
            null,
            $this->registryMock
        );

        $this->assertInstanceOf($expected, $exporter);
    }

    public function testExporterThrowsAnExceptionWhenAnUnknownInstanceIsRequested(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported exporter type.');

        Exporter::buildInstance(
            12345,
            $this->defaultConfig,
            $this->loggerMock,
            $this->loggerMock
        );
    }

    public function testExport()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'English Test Store',
            'pluginSetId' => 44,
            'configuration' => ['displayItemName' => 1, 'defaultLanguage' => 'en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $expectedItems = $this->getExpectedCsvItems();

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    public function testCorrectLanguageIsUsed()
    {
        $this->defaultConfig->setLanguage('de');
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'Test Store',
            'pluginSetId' => 44,
            'configuration' => ['displayItemName' => 1, 'defaultLanguage' => 'en', 'languageList' => 'de,en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $expectedItems = $this->getExpectedCsvItems(true);

        $expectedItems[0]->addName('de1');
        $expectedItems[0]->addSummary('de-shortdescription');
        $expectedItems[0]->addDescription('de-description');
        $expectedItems[0]->addUrl('https://plenty-testshop.de/de/de-urlpath/a-102');
        $expectedItems[0]->setAllKeywords([
            new Keyword('Germany First Tage'),
            new Keyword('Germany'),
            new Keyword('Zeta Tage')
        ]);
        $expectedItems[0]->addAttribute(new Attribute('cat', ['Sessel & Hocker']));
        $expectedItems[0]->addAttribute(new Attribute('Description', ['de-Changes']));
        $expectedItems[0]->addAttribute(new Attribute('Size', ['Sehr Grob']));
        $expectedItems[0]->addAttribute(new Attribute('Fourth Property', ['Also Nice']));

        $expectedItems[1]->addName('de-Brown armchair »New York« with real leather upholstery');
        $expectedItems[1]->addSummary('de-Upholstery: pigmented Napa leather (100% leather) Upholstery color: brown');
        $expectedItems[1]->addDescription('de-deeeeescription');
        $expectedItems[1]->addUrl(
            'https://plenty-testshop.de/de/de-brown-armchair-new-york-with-real-leather-upholstery/a-103'
        );
        $expectedItems[1]->setAllKeywords([
            new Keyword('Germany First Tage'),
            new Keyword('Zeta Tage')
        ]);
        $expectedItems[1]->addAttribute(new Attribute('cat', ['Sessel & Hocker']));
        $expectedItems[1]->addAttribute(new Attribute('test-multiselect-property', ['de-value2', 'de-value1']));

        $expectedItems[2]->addUrl('https://plenty-testshop.de/de/leather-sofa-san-jose-brown/a-104');
        $expectedItems[2]->setAllKeywords([
            new Keyword('Germany First Tage'),
            new Keyword('Germany'),
            new Keyword('Zeta Tage')
        ]);

        $expectedItems[3]->addUrl(
            'https://plenty-testshop.de/de/designer-executive-chair-brookhaven-black-leather/a-105'
        );
        $expectedItems[3]->addAttribute(new Attribute('cat', ['Bürostühle']));

        $expectedItems[4]->addUrl('https://plenty-testshop.de/de/modern-office-chair-merrick-green/a-106');
        $expectedItems[4]->setAllKeywords([]);
        $expectedItems[4]->addAttribute(new Attribute('cat', ['Bürostühle', 'Sessel & Hocker']));


        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    public function testItAddsLanguageCodeToUrlWhenNonDefaultLanguageIsSelected()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['displayItemName' => 1, 'defaultLanguage' => 'fr', 'languageList' => 'fr,en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $expectedItems = $this->getExpectedCsvItems();
        foreach ($expectedItems as $expectedItem) {
            $url = $expectedItem->getUrl()->getValues();
            $url = reset($url);
            if (!$url) {
                continue;
            }
            $expectedItem->addUrl(str_replace('.de/', '.de/en/', $url));
        }

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    public function testItDoesNotAddTextsWhenDisplayItemNameIsNotSet()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['defaultLanguage' => 'en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $expectedItems = $this->getExpectedCsvItems();
        foreach ($expectedItems as $expectedItem) {
            $expectedItem->setSummary(new Summary());
            $expectedItem->setDescription(new Description());
            $expectedItem->setName(new Name());
            $expectedItem->setUrl(new Url());
        }

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    public function testItDoesNotExportItemsWithNoPriceOnMainVariation()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][0]['variationSalesPrices'] = [];
        $itemVariationResponse = new Response(200, [], json_encode($rawResponse));
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['defaultLanguage' => 'en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $expectedItems = $this->getExpectedCsvItems();
        array_shift($expectedItems);
        foreach ($expectedItems as $expectedItem) {
            $expectedItem->setSummary(new Summary());
            $expectedItem->setDescription(new Description());
            $expectedItem->setName(new Name());
            $expectedItem->setUrl(new Url());
        }

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    public function testItThrowsAnExceptionWhenMainVariationHasNoImage()
    {
        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][0]['itemImages'] = [];
        $itemVariationResponse = new Response(200, [], json_encode($rawResponse));
        $variationCount = count(ItemVariationParser::parse($itemVariationResponse)->all());

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['defaultLanguage' => 'en']
        ]);

        $this->setupRegistryMock($webStore, $variationCount);

        $this->expectException(\TypeError::class);

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);

        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);
        $exporter->export();
    }

    /**
     * @return CSVItem[]
     */
    private function getExpectedCsvItems(bool $translationFlag = false): array
    {
        $csvItem1 = $this->fileExporterMock->createItem(102);
        $csvItem1->addName('1');
        $csvItem1->addSummary('shortdescription');
        $csvItem1->addDescription('description');
        $csvItem1->addUrl('https://plenty-testshop.de/urlpath/a-102');
        $csvItem1->addSalesFrequency(0);
        $csvItem1->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));
        $csvItem1->addPrice(269.99);
        $csvItem1->setInsteadPrice(320);
        $csvItem1->addSort(0);
        $csvItem1->addAttribute(new Attribute('cat', ['Armchairs & Stools']));
        $csvItem1->addAttribute(new Attribute('cat_url', ['https://plentydemoshop.com/wohnzimmer/sessel-hocker/']));
        $csvItem1->addAttribute(new Attribute('Couch color', ['black']));
        $csvItem1->addAttribute(new Attribute('cat_id', ['1', '2', '3']));
        $csvItem1->addAttribute(new Attribute('Test Group', ['Third Property']));
        $csvItem1->addAttribute(new Attribute('Description', ['Changes']));
        $csvItem1->addAttribute(new Attribute('Size', ['Very Large']));
        $csvItem1->addAttribute(new Attribute('Fourth Property', ['Nice']));
        $name = $translationFlag ? 'something else' : 'something';
        $csvItem1->addAttribute(new Attribute($name, [$name]));
        $csvItem1->addAttribute(new Attribute($translationFlag ? 'named propertyy' : 'named property', ['123']));
        $csvItem1->addImage(new Image('https://images.com/middle/image.jpg'));
        $csvItem1->addUsergroup(new Usergroup('0_'));
        $csvItem1->addKeyword(new Keyword('aaaa'));
        $csvItem1->addKeyword(new Keyword('Second TAge'));
        $csvItem1->addKeyword(new Keyword('Zeta Tage'));

        $csvItem2 = $this->fileExporterMock->createItem(103);
        $csvItem2->addName("Brown armchair »New York« with real leather upholstery");
        $csvItem2->addSummary("Upholstery: pigmented Napa leather (100% leather)\nUpholstery color: brown");
        $csvItem2->addDescription('deeeeescription');
        $csvItem2->addUrl('https://plenty-testshop.de/brown-armchair-new-york-with-real-leather-upholstery/a-103');
        $csvItem2->addSalesFrequency(0);
        $csvItem2->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));
        $csvItem2->addPrice(499);
        $csvItem2->setInsteadPrice(560);
        $csvItem2->addSort(0);
        $csvItem2->addAttribute(new Attribute('cat', ['Armchairs & Stools']));
        $csvItem2->addAttribute(new Attribute('cat_url', ['https://plentydemoshop.com/wohnzimmer/sessel-hocker/']));
        $csvItem2->addAttribute(new Attribute('Couch color', ['purple']));
        $csvItem2->addAttribute(new Attribute('cat_id', ['1', '3']));
        $csvItem2->addAttribute(new Attribute('Test Group', ['Third Property', 'PropertyWithAWeirdType']));
        $csvItem2->addAttribute(new Attribute('test-multiselect-property', ['value2', 'value1']));
        $csvItem2->addAttribute(new Attribute('Float Property', ['123.45']));
        $csvItem2->addAttribute(new Attribute('Test', [100]));
        $prefix = $translationFlag ? 'de-' : '';
        $csvItem2->addAttribute(new Attribute($prefix . 'Some group', [$prefix . 'Some property of empty type']));
        $csvItem2->addImage(
            new Image('https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/103/middle/103-sessel-braun-1.jpg')
        );
        $csvItem2->addUsergroup(new Usergroup('0_'));
        $csvItem2->addKeyword(new Keyword('aaaa'));
        $csvItem2->addKeyword(new Keyword('Zeta Tage'));

        $csvItem3 = $this->fileExporterMock->createItem(104);
        $csvItem3->addName("Leather sofa »San Jose« brown");
        $csvItem3->addSummary("Elegant padded furniture made of real leather");
        $csvItem3->addDescription('<p style="text-align: left;">Luxurious sofas made of elegant leather</p>');
        $csvItem3->addUrl('https://plenty-testshop.de/leather-sofa-san-jose-brown/a-104');
        $csvItem3->addSalesFrequency(0);
        $csvItem3->addPrice(639);
        $csvItem3->setInsteadPrice(710);
        $csvItem3->addSort(0);
        $csvItem3->addAttribute(new Attribute('cat', ['Sofas']));
        $csvItem3->addAttribute(new Attribute('cat_url', ['https://plentydemoshop.com/wohnzimmer/sofas/']));
        $csvItem3->addAttribute(new Attribute('Couch color', ['purple']));
        $csvItem3->addAttribute(new Attribute('cat_id', ['1', '2', '3']));
        $csvItem3->addAttribute(new Attribute('Test Group', ['Third Property']));
        $csvItem3->addImage(
            new Image(
                'https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/104/middle/104-couche-leder-braun-1.jpg'
            )
        );
        $csvItem3->addUsergroup(new Usergroup('0_'));
        $csvItem3->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));
        $csvItem3->addKeyword(new Keyword('aaaa'));
        $csvItem3->addKeyword(new Keyword('Second TAge'));
        $csvItem3->addKeyword(new Keyword('Zeta Tage'));

        $csvItem4 = $this->fileExporterMock->createItem(105);
        $csvItem4->addName("Designer executive chair »Brookhaven« black leather");
        $csvItem4->addSummary("Swivel office chair");
        $csvItem4->addDescription('asdfasdf165498c79gfdn');
        $csvItem4->addUrl('https://plenty-testshop.de/designer-executive-chair-brookhaven-black-leather/a-105');
        $csvItem4->addSalesFrequency(0);
        $csvItem4->addPrice(415.31);
        $csvItem4->setInsteadPrice(450);
        $csvItem4->addSort(0);
        $csvItem4->addAttribute(new Attribute('cat', ['Office Chairs']));
        $csvItem4->addAttribute(
            new Attribute('cat_url', ['https://plentydemoshop.com/arbeitszimmer-buero/buerostuehle/'])
        );
        $csvItem4->addAttribute(new Attribute('Test Group', ['Third Property']));
        $csvItem4->addAttribute(new Attribute('Test', ['158']));
        $csvItem4->addAttribute(new Attribute('Second Property', ['158221']));
        $csvItem4->addImage(
            new Image(
                'https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/105/middle/105-buerostuhl-schwarz.jpg'
            )
        );
        $csvItem4->addUsergroup(new Usergroup('0_'));
        $csvItem4->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem5 = $this->fileExporterMock->createItem(106);
        $csvItem5->addName("Modern office chair »Merrick« green");
        $csvItem5->addSummary("Classic design and a relaxed style");
        $csvItem5->addDescription('h asdf po gfklj hgfp sfdg');
        $csvItem5->addUrl('https://plenty-testshop.de/modern-office-chair-merrick-green/a-106');
        $csvItem5->addSalesFrequency(0);
        $csvItem5->addPrice(987.65); // Main variation price
        $csvItem5->setInsteadPrice(567.89); // Main variation price
        $csvItem5->addSort(5); // Main variation position
        $csvItem5->addImage(new Image('https://cdn03.plentymarkets.com/main-variation-middle-image.jpg'));
        $csvItem5->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));
        $csvItem5->addUsergroup(new Usergroup('0_'));
        // Categories from both variations
        $csvItem5->addAttribute(new Attribute('cat', ['Office Chairs', 'Armchairs & Stools']));
        $csvItem5->addAttribute(new Attribute('cat_url', [
            'https://plentydemoshop.com/arbeitszimmer-buero/buerostuehle/',
            'https://plentydemoshop.com/wohnzimmer/sessel-hocker/',
        ]));

        return [$csvItem1, $csvItem2, $csvItem3, $csvItem4, $csvItem5];
    }

    private function setupRegistryMock(WebStore $webStore, int $variationsCount): void
    {
        $categoryResponse = $this->getMockResponse('CategoryResponse/response.json');
        $categories = CategoryParser::parse($categoryResponse);

        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/response.json');
        $salesPrices = SalesPriceParser::parse($salesPriceResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);

        $propertyResponse = $this->getMockResponse('PropertyResponse/response.json');
        $properties = PropertyParser::parse($propertyResponse);

        $itemPropertyResponse = $this->getMockResponse('ItemPropertyResponse/response.json');
        $itemProperties = ItemPropertyParser::parse($itemPropertyResponse);

        $unitResponse = $this->getMockResponse('UnitResponse/response.json');
        $units = UnitParser::parse($unitResponse);

        $storesResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $stores = WebStoreParser::parse($storesResponse);

        $propertyGroupResponse = $this->getMockResponse('PropertyGroupResponse/response.json');
        $propertyGroups = PropertyGroupParser::parse($propertyGroupResponse);

        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $propertySelections = PropertySelectionParser::parse($propertySelectionResponse);

        $returnsQueue = [
            $categories,
            $salesPrices,
            $attributes,
            $itemProperties,
            $properties,
            $units,
            $webStore
        ];

        for ($i = 0; $i < $variationsCount; $i++) {
            $returnsQueue[] = $propertyGroups;
            $returnsQueue[] = $propertySelections;
            $returnsQueue[] = $categories;
            $returnsQueue[] = $salesPrices;
            $returnsQueue[] = $attributes;
            $returnsQueue[] = $stores;
            $returnsQueue[] = $properties;
        }

        $this->registryMock->method('get')->willReturnOnConsecutiveCalls(...$returnsQueue);
    }
}
