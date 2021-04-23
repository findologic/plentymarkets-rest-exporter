<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use FINDOLOGIC\Export\Exporter as LibFlexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ExporterTest extends TestCase
{
    use ResponseHelper;

    private const EXPORTER_LOCATION = '/tmp/rest-exporter/';

    /** @var Config */
    private $config;

    /** @var DummyLogger */
    private $logger;

    /** @var Client|MockObject */
    private $clientMock;

    /** @var RegistryService|MockObject */
    private $registryServiceMock;

    /** @var ItemRequest|MockObject */
    private $itemRequestMock;

    /** @var PimVariationRequest|MockObject */
    private $variationRequestMock;

    /** @var LibFlexportExporter|MockObject */
    private $fileExporterMock;

    /** @var null|string */
    private $fileNamePrefix;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->config->setLanguage('de');
        $this->logger = new DummyLogger();
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->variationRequestMock = $this->getMockBuilder(PimVariationRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileExporterMock = $this->getMockBuilder(LibFlexportExporter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileNamePrefix = null;

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->any())->method('getStandardVat')->willReturn($standardVat);

        $categoryResponse = $this->getMockResponse('CategoryResponse/one.json');
        $parsedCategoryResponse = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getCategory')
            ->willReturn($parsedCategoryResponse->first());
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
     */
    public function testProperInstanceIsCreated(int $type, string $expected): void
    {
        $exporter = Exporter::buildInstance($type, $this->config, $this->logger, $this->logger);

        $this->assertInstanceOf($expected, $exporter);
    }

    /**
     * @dataProvider exporterTypeProvider
     */
    public function testExportWorksProperly(int $type, string $expected): void
    {
        $this->setUpClientMock();

        $exporter = $this->getExporter($type);
        $exporter->export();

        $this->assertInstanceOf($expected, $exporter);
    }

    public function testFileNameIsChangedWhenSet(): void
    {
        $this->fileNamePrefix = 'findologic.new.funny';
        $this->fileExporterMock = LibFlexportExporter::create(LibFlexportExporter::TYPE_CSV);
        $expectedFileLocation = self::EXPORTER_LOCATION . $this->fileNamePrefix . '.csv';

        $this->setUpClientMock();
        $exporter = $this->getExporter(Exporter::TYPE_CSV);
        $exporter->export();

        $this->assertStringContainsString(
            "id\tordernumber\tname",
            file_get_contents($expectedFileLocation)
        );

        // Remove CSV file after test.
        unlink($expectedFileLocation);
    }

    public function testExporterThrowsAnExceptionWhenAnUnknownInstanceIsRequested(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported exporter type.');

        Exporter::buildInstance(
            12345,
            $this->config,
            $this->logger,
            $this->logger
        );
    }

    /**
     * @dataProvider exporterTypeProvider
     */
    public function testExportTimeIsReturned(int $type): void
    {
        $exporter = $this->getExporter($type);
        $this->assertSame('00:00:00', $exporter->getExportTime());
    }

    /**
     * @dataProvider exporterTypeProvider
     */
    public function testWrapperCanBeUsedToGetTheExportPath(int $type): void
    {
        if ($type === Exporter::TYPE_XML) {
            $this->markTestSkipped('Skipped until XML is implemented.');
        }

        $exporter = $this->getExporter($type);
        $this->assertSame(self::EXPORTER_LOCATION, $exporter->getWrapper()->getExportPath());
    }

    protected function getExporter(int $type): Exporter
    {
        return Exporter::buildInstance(
            $type,
            $this->config,
            $this->logger,
            $this->logger,
            self::EXPORTER_LOCATION,
            $this->fileNamePrefix,
            $this->clientMock,
            $this->registryServiceMock,
            $this->itemRequestMock,
            $this->variationRequestMock,
            $this->fileExporterMock
        );
    }

    protected function setUpClientMock(): void
    {
        $this->clientMock->expects($this->any())->method('send')->willReturnCallback(
            function (RequestInterface $request) {
                switch (true) {
                    case $request instanceof ItemRequest:
                        return $this->getMockResponse('ItemResponse/response.json');
                    case $request instanceof PimVariationRequest:
                        return $this->getMockResponse('Pim/Variations/response.json');
                    default:
                        throw new InvalidArgumentException(sprintf(
                            'No client response set up for request class %s.',
                            get_class($request)
                        ));
                }
            }
        );
    }
}
