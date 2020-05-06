<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertiesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPricesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributesParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturersParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\CategoryRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\VatRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertiesRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\SalesPricesRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\AttributesRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ManufacturersRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\VatResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertiesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\SalesPricesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ManufacturersResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class Exporter
{
    public const
        TYPE_CSV = 0,
        TYPE_XML = 1;

    /** @var LoggerInterface */
    protected $internalLogger;

    /** @var LoggerInterface */
    protected $customerLogger;

    /** @var Config */
    protected $config;

    /** @var Client */
    protected $client;

    /** @var Registry */
    protected $registry;

    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
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
     * @param LoggerInterface $internalLogger
     * @param LoggerInterface $customerLogger
     * @param Client|null $client
     * @param Registry|null $registry
     *
     * @return Exporter
     */
    public static function buildInstance(
        int $type,
        Config $config,
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
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
        $this->customerLogger->info('Starting to initialise necessary data (categories, attributes, etc.).');

        $this->registry->set('webStore', $this->getWebStore());
        $this->registry->set('categories', $this->getCategories());
        $this->registry->set('vat', $this->getVat());
        $this->registry->set('salesPrices', $this->getSalesPrices());
        $this->registry->set('attributes', $this->getAttributes());
        $this->registry->set('manufacturers', $this->getManufacturers());
        $this->registry->set('properties', $this->getProperties());
    }

    private function getWebStore(): WebStore
    {
        $webStoreRequest = new WebStoreRequest();
        $response = $this->client->send($webStoreRequest);

        $webStores = WebStoreParser::parse($response);
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

    private function getCategories(): CategoryResponse
    {
        /** @var WebStore $webStore */
        $webStore = $this->registry->get('webStore');

        $categoryRequest = new CategoryRequest($webStore->getStoreIdentifier());

        $categories = [];
        foreach (Utils::sendIterableRequest($this->client, $categoryRequest) as $response) {
            $categoryResponse = CategoryParser::parse($response);
            $categoriesMatchingCriteria = $categoryResponse->find([
                'details' => [
                    'lang' => $this->config->getLanguage(),
                    'plentyId' => $webStore->getStoreIdentifier()
                ]
            ]);

            $categories = array_merge($categories, $categoriesMatchingCriteria);
        }

        return new CategoryResponse(1, count($categories), true, $categories);
    }

    private function getVat(): VatResponse
    {
        $vatRequest = new VatRequest();

        $vatConfigurations = [];

        foreach (Utils::sendIterableRequest($this->client, $vatRequest) as $response) {
            $vatResponse = VatParser::parse($response);
            $vatConfigurations = array_merge($vatResponse->all(), $vatConfigurations);
        }

        return new VatResponse(1, count($vatConfigurations), true, $vatConfigurations);
    }

    private function getSalesPrices(): SalesPricesResponse
    {
        $salesPricesRequest = new SalesPricesRequest();

        $salesPrices = [];

        foreach (Utils::sendIterableRequest($this->client, $salesPricesRequest) as $response) {
            $salesPricesResponse = SalesPricesParser::parse($response);
            $salesPrices = array_merge($salesPricesResponse->all(), $salesPrices);
        }

        return new SalesPricesResponse(1, count($salesPrices), true, $salesPrices);
    }

    private function getAttributes(): AttributesResponse
    {
        $attributesRequest = new AttributesRequest();

        $attributes = [];

        foreach (Utils::sendIterableRequest($this->client, $attributesRequest) as $response) {
            $attributesResponse = AttributesParser::parse($response);
            $attributes = array_merge($attributesResponse->all(), $attributes);
        }

        return new AttributesResponse(1, count($attributes), true, $attributes);
    }

    private function getManufacturers(): ManufacturersResponse
    {
        $manufacturersRequest = new ManufacturersRequest();

        $manufacturers = [];

        foreach (Utils::sendIterableRequest($this->client, $manufacturersRequest) as $response) {
            $manufacturersResponse = ManufacturersParser::parse($response);
            $manufacturers = array_merge($manufacturersResponse->all(), $manufacturers);
        }

        return new ManufacturersResponse(1, count($manufacturers), true, $manufacturers);
    }

    private function getProperties(): PropertiesResponse
    {
        //TODO: clarification needed - documentation specifies this request as non-iterable, however it seems to be. Treating as iterable
        $propertiesRequest = new PropertiesRequest();

        $properties = [];

        foreach (Utils::sendIterableRequest($this->client, $propertiesRequest) as $response) {
            $propertiesResponse = PropertiesParser::parse($response);
            $properties = array_merge($propertiesResponse->all(), $properties);
        }

        return new PropertiesResponse(1, count($properties), true, $properties);
    }
}
