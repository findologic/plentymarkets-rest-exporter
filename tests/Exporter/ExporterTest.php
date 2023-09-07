<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use Exception;
use FINDOLOGIC\Export\Enums\ExporterType;
use FINDOLOGIC\Export\Exporter as LibFlexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use InvalidArgumentException;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ExporterTest extends TestCase
{
    use ResponseHelper;

    private const EXPORTER_LOCATION = '/tmp/rest-exporter/';

    private Config $config;

    private ?string $fileNamePrefix;

    private DummyLogger|MockObject $logger;

    private Client|MockObject $clientMock;

    private RegistryService|MockObject $registryServiceMock;

    private ItemRequest|MockObject $itemRequestMock;

    private PimVariationRequest|MockObject $variationRequestMock;

    private LibFlexportExporter|MockObject $fileExporterMock;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->config->setLanguage('de');
        $this->config->setUseVariants(false);
        $this->logger = new DummyLogger();
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemRequestMock = $this->getMockBuilder(ItemRequest::class)
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

        $webstoreResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $parsedWebstoreResponse = WebStoreParser::parse($webstoreResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getWebstore')
            ->willReturn($parsedWebstoreResponse->first());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_ENV['APP_ENV'] = 'test';
    }

    public function testProperInstanceIsCreated(): void
    {
        $exporter = Exporter::buildInstance($this->config, $this->logger, $this->logger);

        $this->assertInstanceOf(XmlExporter::class, $exporter);
    }

    public function testExportWorksProperly(): void
    {
        $this->setUpClientMock();

        $exporter = $this->getExporter();
        $exporter->export();

        $this->assertInstanceOf(XmlExporter::class, $exporter);
    }

    public function testExceptionIsThrownForTestEnvironment(): void
    {
        $expectedExceptionMessage = 'Something gone real bad...';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->clientMock->expects($this->any())
            ->method('send')
            ->willThrowException(new Exception($expectedExceptionMessage));

        $exporter = $this->getExporter();
        $exporter->export();
    }

    public function testExceptionIsThrownForDevEnvironment(): void
    {
        $expectedExceptionMessage = 'Something gone real bad...';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $_ENV['APP_ENV'] = 'dev';
        $this->clientMock->expects($this->any())
            ->method('send')
            ->willThrowException(new Exception($expectedExceptionMessage));

        $exporter = $this->getExporter();
        $exporter->export();
    }

    public function testExceptionIsCaughtForProdEnvironment(): void
    {
        $expectedExceptionMessage = 'Something gone real bad...';
        $expectedException = new Exception($expectedExceptionMessage);
        $expectedExceptionContent = sprintf(
            'An unexpected error occurred. Export will stop. %s: %s. Stack trace: %s',
            get_class($expectedException),
            $expectedException->getMessage(),
            $expectedException->getTraceAsString()
        );

        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive(
                ['An unexpected error occurred. Export will stop.'],
                [
                    $expectedExceptionContent,
                    ['exception' => $expectedException]
                ]
            );

        $_ENV['APP_ENV'] = 'prod';
        $this->clientMock->expects($this->any())
            ->method('send')
            ->willThrowException($expectedException);

        $exporter = $this->getExporter();
        $result = $exporter->export();

        $this->assertSame(Exporter::FAILURE, $result);
    }

    public function testFileNameIsChangedWhenSet(): void
    {
        $this->fileNamePrefix = 'findologic.new.funny';
        $this->fileExporterMock = LibFlexportExporter::create(ExporterType::XML);

        $this->setUpClientMock();
        $exporter = $this->getExporter();
        $exporter->export();
        $exportPath = $exporter->getWrapper()->getExportPath();
        $this->assertStringContainsString(
            '<?xml version="1.0" encoding="utf-8"?>',
            file_get_contents($exportPath)
        );

        // Remove XML file after test.
        unlink($exportPath);
    }

    public function testExportTimeIsReturned(): void
    {
        $exporter = $this->getExporter();
        $this->assertSame('00:00:00', $exporter->getExportTime());
    }

    public function testWrapperCanBeUsedToGetTheExportPath(): void
    {
        $exporter = $this->getExporter();
        $this->assertSame(self::EXPORTER_LOCATION, $exporter->getWrapper()->getExportPath());
    }

    protected function getExporter(): Exporter
    {
        return Exporter::buildInstance(
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
                return match (true) {
                    $request instanceof ItemRequest => $this->getMockResponse('ItemResponse/response.json'),
                    $request instanceof PimVariationRequest => $this->getMockResponse('Pim/Variations/response.json'),
                    default => throw new InvalidArgumentException(sprintf(
                        'No client response set up for request class %s.',
                        get_class($request)
                    )),
                };
            }
        );
    }
}
