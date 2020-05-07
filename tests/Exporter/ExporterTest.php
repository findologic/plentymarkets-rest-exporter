<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\VatResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration;
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

    private function getDefaultExporter(int $type): Exporter
    {
        return Exporter::buildInstance(
            $type,
            $this->defaultConfig,
            $this->loggerMock,
            $this->loggerMock,
            $this->clientMock,
            $this->registryMock
        );
    }

    public function setUp(): void
    {
        $this->defaultConfig = $this->getDefaultConfig();
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

    public function exporterTypeRegistryProvider(): array
    {
        return [
            'Exporter type is CSV' => [
                'type' => Exporter::TYPE_CSV,
            ],
            'Exporter type is XML' => [
                'type' => Exporter::TYPE_XML,
            ],
        ];
    }

    /**
     * @dataProvider exporterTypeRegistryProvider
     */
    public function testRegistryIsWarmedUp(int $type): void
    {
        $exporter = $this->getDefaultExporter($type);

        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);
        $webStoreResponseBody = [
            $expectedWebStore->getData(),
            [
                'id' => 1,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));

        $categoryResponseBody = json_decode(
            $this->getMockResponse('CategoryResponse/response.json')->getBody()->__toString(),
            true
        );
        $expectedCategories = new CategoryResponse(
            1,
            0,
            true,
            [] // All categories are filtered out by the criteria.
        );
        $categoryResponse = new Response(200, [], json_encode($categoryResponseBody));

        $vatResponse = $this->getMockResponse('VatResponse/response.json');
        $expectedVat = VatParser::parse($vatResponse);

        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/response.json');
        $expectedSalesPrice = SalesPriceParser::parse($salesPriceResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $expectedAttribute = AttributeParser::parse($attributeResponse);

        $manufacturerResponse = $this->getMockResponse('ManufacturerResponse/response.json');
        $expectedManufacturer = ManufacturerParser::parse($manufacturerResponse);

        $propertyResponse = $this->getMockResponse('PropertyResponse/response.json');
        $expectedProperties = PropertyParser::parse($propertyResponse);

        $itemPropertyResponse = $this->getMockResponse('ItemPropertyResponse/response.json');
        $expectedItemProperties = ItemPropertyParser::parse($itemPropertyResponse);

        $unitResponse = $this->getMockResponse('UnitResponse/response.json');
        $expectedUnits = UnitParser::parse($unitResponse);

        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $expectedPropertySelections = PropertySelectionParser::parse($propertySelectionResponse);

        $this->clientMock->expects($this->exactly(10))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $webStoreResponse,
                $categoryResponse,
                $vatResponse,
                $salesPriceResponse,
                $attributeResponse,
                $manufacturerResponse,
                $propertyResponse,
                $itemPropertyResponse,
                $unitResponse,
                $propertySelectionResponse
            );

        $this->registryMock->expects($this->exactly(10))
            ->method('set')
            ->withConsecutive(
                ['webStore', $expectedWebStore],
                ['categories', $expectedCategories],
                ['vat', $expectedVat],
                ['salesPrices', $expectedSalesPrice],
                ['attributes', $expectedAttribute],
                ['manufacturers', $expectedManufacturer],
                ['properties', $expectedProperties],
                ['itemProperties', $expectedItemProperties],
                ['units', $expectedUnits],
                ['propertySelections', $expectedPropertySelections]
            );

        $this->registryMock->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls($expectedWebStore);

        $exporter->export();
    }

    /**
     * @dataProvider exporterTypeRegistryProvider
     */
    public function testExporterFailsIfWebStoreDoesNotExist(int $type): void
    {
        $expectedMultiShopId = 1337;

        $this->expectException(CustomerException::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find a web store with the multishop id "%d"',
            $expectedMultiShopId
        ));

        $exporter = $this->getDefaultExporter($type);

        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);
        $webStoreResponseBody = [
            $expectedWebStore->getData(),
            [
                'id' => 1,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));

        $this->clientMock->expects($this->once())
            ->method('send')
            ->willReturnOnConsecutiveCalls($webStoreResponse);

        $this->defaultConfig->setMultiShopId($expectedMultiShopId);

        $exporter->export();
    }
}
