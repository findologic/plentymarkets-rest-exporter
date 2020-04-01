<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStoreEntity;
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

    /** @var Registry */
    protected $registry;

    public function __construct(
        Logger $internalLogger,
        Logger $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = $client ?? new Client(new GuzzleClient(), $config, $internalLogger, $customerLogger);
        $this->registry = $registry ?? new Registry();
    }

    /**
     * Builds a new exporter instance. Use Exporter::TYPE_CSV / Exporter::TYPE_XML to get the respective type.
     *
     * @param int $type Exporter::TYPE_CSV / Exporter::TYPE_XML
     * @param Config $config
     * @param Logger $internalLogger
     * @param Logger $customerLogger
     * @param Client|null $client
     * @param Registry|null $registry
     *
     * @return Exporter
     */
    public static function buildInstance(
        int $type,
        Config $config,
        Logger $internalLogger,
        Logger $customerLogger,
        ?Client $client = null,
        ?Registry $registry = null
    ): Exporter {
        switch ($type) {
            case self::TYPE_CSV:
                return new CsvExporter($internalLogger, $customerLogger, $config, $client, $registry);
            case self::TYPE_XML:
                return new XmlExporter($internalLogger, $customerLogger, $config, $client, $registry);
            default:
                throw new InvalidArgumentException('Unknown or unsupported exporter type.');
        }
    }

    public function export(): void
    {
        $this->warmUpRegistry();
    }

    protected function warmUpRegistry(): void
    {
        $this->registry->set('webStore', $this->getWebStore());
    }

    private function getWebStore(): WebStoreEntity
    {
        $webStores = $this->client->getWebStores();

        $webStores->parse();
        $webStore = $webStores->findOne([
            'id' => $this->config->getMultiShopId()
        ]);

        if (!$webStore) {
            throw new CustomerException(sprintf(
                'Could not find a web store with the multishop id "%d"',
                $this->config->getMultiShopId()
            ));
        }

        return $webStore;
    }
}
