<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\CsvExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExporterTest extends TestCase
{
    /** @var Config */
    private $defaultConfig;

    /** @var Logger|MockObject */
    private $loggerMock;

    public function setUp(): void
    {
        $this->defaultConfig = new Config();
        $this->loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
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
}
