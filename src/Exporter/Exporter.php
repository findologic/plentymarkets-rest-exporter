<?php

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Log4Php\Logger;

abstract class Exporter
{
    public const
        TYPE_CSV = 0,
        TYPE_XML = 1;

    /** @var Logger */
    protected $internalLogger;

    /** @var Logger */
    protected $customerLogger;

    /** @var Config */
    protected $config;

    /** @var Client */
    protected $client;

    public function __construct(
        Logger $internalLogger,
        Logger $customerLogger,
        Config $config,
        ?GuzzleClient $httpClient = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = new Client($httpClient ?? new GuzzleClient(), $config, $internalLogger, $customerLogger);
    }

    /**
     * Builds a new exporter instance. Use Exporter::TYPE_CSV / Exporter::TYPE_XML to get the respective type.
     *
     * @param int $type Exporter::TYPE_CSV / Exporter::TYPE_XML
     * @param Config $config
     * @param Logger $internalLogger
     * @param Logger $customerLogger
     *
     * @return Exporter
     */
    public static function buildInstance(
        int $type,
        Config $config,
        Logger $internalLogger,
        Logger $customerLogger
    ): Exporter {
        switch ($type) {
            case self::TYPE_CSV:
                return new CsvExporter($internalLogger, $customerLogger, $config);
            case self::TYPE_XML:
                return new XmlExporter($internalLogger, $customerLogger, $config);
            default:
                throw new InvalidArgumentException(sprintf('Unknown or unsupported exporter type.'));
        }
    }

    public function export(): void
    {
        $this->customerLogger->info('Getting standard information like webstores, etc.');
        $webStores = $this->client->getWebStores();
        $webStores->serialize();
        var_dump($webStores);
    }

    private function fetchGeneralData(): void
    {

    }
}
