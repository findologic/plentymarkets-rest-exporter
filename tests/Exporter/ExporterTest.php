<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStoreEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\WebStoreResponse;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExporterTest extends TestCase
{
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
        $this->defaultConfig = new Config([
            'domain' => 'plenty-testshop.de',
            'username' => 'user',
            'password' => 'pretty secure, I think!',
            'language' => 'de',
            'multiShopId' => 0,
            'availabilityId' => null,
            'priceId' => null,
            'rrpId' => null,
        ]);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->onlyMethods(['getWebStores'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set'])
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
    public function testRegistryIsWarmedUpWithWebStore(int $type): void
    {
        $exporter = $this->getDefaultExporter($type);

        $expectedWebStore = new WebStoreEntity([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);

        $responseBody = [
            $expectedWebStore->jsonSerialize(),
            [
                'id' => 1,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];

        $response = new Response(200, [], json_encode($responseBody));
        $webStores = new WebStoreResponse($response);

        $this->clientMock->expects($this->once())
            ->method('getWebStores')
            ->willReturn($webStores);

        $this->registryMock->expects($this->once())
            ->method('set')
            ->with('webStore', $expectedWebStore);

        $exporter->export();
    }
}
