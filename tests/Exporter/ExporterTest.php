<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use FINDOLOGIC\Export\CSV\CSVExporter as CsvFileExporter;
use FINDOLOGIC\Export\CSV\CSVItem;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
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
        $exporter = Exporter::buildInstance(
            $type,
            $this->defaultConfig,
            $this->loggerMock,
            $this->loggerMock
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
        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => ['displayItemName' => 1, 'defaultLanguage' => 'en']
        ]);

        $this->registryMock->method('get')->willReturn($webStore);

        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');

        $expectedItems = $this->getExpectedCsvItems();

        $this->clientMock->method('send')->willReturnOnConsecutiveCalls($itemResponse, $itemVariationResponse);
        $this->fileExporterMock->expects($this->once())->method('serializeItemsToFile')->with(
            'exportpath',
            $expectedItems
        );

        $exporter->export();
    }

    public function testItAddsLanguageCodeToUrlWhenNonDefaultLanguageIsSelected()
    {
        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['displayItemName' => 1, 'defaultLanguage' => 'fr', 'languageList' => 'fr,en']
        ]);

        $this->registryMock->method('get')->willReturn($webStore);

        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');

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

        $exporter->export();
    }

    public function testItDoesNotAddTextsWhenDisplayItemNameIsNotSet()
    {
        $exporter = $this->getDefaultExporter(Exporter::TYPE_CSV);

        $webStore = new WebStore([
            'id' => 1,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'French Test Store',
            'pluginSetId' => 44,
            'configuration' => ['defaultLanguage' => 'en']
        ]);

        $this->registryMock->method('get')->willReturn($webStore);

        $itemResponse = $this->getMockResponse('ItemResponse/response.json');
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');

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

        $exporter->export();
    }

    /**
     * @return CSVItem[]
     */
    private function getExpectedCsvItems(): array
    {
        $csvItem1 = $this->fileExporterMock->createItem(102);
        $csvItem1->addName('1');
        $csvItem1->addSummary('shortdescription');
        $csvItem1->addDescription('description');
        $csvItem1->addUrl('https://plenty-testshop.de/urlpath/a-102');
        $csvItem1->addSalesFrequency(0);
        $csvItem1->addPrice(13.37);
        $csvItem1->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem2 = $this->fileExporterMock->createItem(103);
        $csvItem2->addName("Brown armchair »New York« with real leather upholstery");
        $csvItem2->addSummary("Upholstery: pigmented Napa leather (100% leather)\nUpholstery color: brown");
        $csvItem2->addDescription('deeeeescription');
        $csvItem2->addUrl('https://plenty-testshop.de/brown-armchair-new-york-with-real-leather-upholstery/a-103');
        $csvItem2->addSalesFrequency(0);
        $csvItem2->addPrice(13.37);
        $csvItem2->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem3 = $this->fileExporterMock->createItem(104);
        $csvItem3->addName("Leather sofa »San Jose« brown");
        $csvItem3->addSummary("Elegant padded furniture made of real leather");
        $csvItem3->addDescription('<p style="text-align: left;">Luxurious sofas made of elegant leather</p>');
        $csvItem3->addUrl('https://plenty-testshop.de/leather-sofa-san-jose-brown/a-104');
        $csvItem3->addSalesFrequency(0);
        $csvItem3->addPrice(13.37);
        $csvItem3->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem4 = $this->fileExporterMock->createItem(105);
        $csvItem4->addName("Designer executive chair »Brookhaven« black leather");
        $csvItem4->addSummary("Swivel office chair");
        $csvItem4->addDescription('asdfasdf165498c79gfdn');
        $csvItem4->addUrl('https://plenty-testshop.de/designer-executive-chair-brookhaven-black-leather/a-105');
        $csvItem4->addSalesFrequency(0);
        $csvItem4->addPrice(13.37);
        $csvItem4->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem5 = $this->fileExporterMock->createItem(106);
        $csvItem5->addName("Modern office chair »Merrick« green");
        $csvItem5->addSummary("Classic design and a relaxed style");
        $csvItem5->addDescription('h asdf po gfklj hgfp sfdg');
        $csvItem5->addUrl('https://plenty-testshop.de/modern-office-chair-merrick-green/a-106');
        $csvItem5->addSalesFrequency(0);
        $csvItem5->addPrice(13.37);
        $csvItem5->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        $csvItem6 = $this->fileExporterMock->createItem(107);
        $csvItem6->setSummary(new Summary());
        $csvItem6->setDescription(new Description());
        $csvItem6->setName(new Name());
        $csvItem6->setUrl(new Url());
        $csvItem6->addSalesFrequency(0);
        $csvItem6->addPrice(13.37);
        $csvItem6->addDateAdded(new \DateTime('2014-12-24T00:00:00+00:00'));

        return [$csvItem1, $csvItem2, $csvItem3, $csvItem4, $csvItem5, $csvItem6];
    }
}
