<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\CategoryRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\VatRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertyRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertySelectionRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\SalesPriceRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\AttributeRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ManufacturerRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemPropertyRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\UnitRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\VatResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertyResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\SalesPriceResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\AttributeResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ManufacturerResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemPropertyResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\UnitResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;
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
        $this->registry->set('itemProperties', $this->getItemProperties());
        $this->registry->set('units', $this->getUnits());
        $this->registry->set('propertySelections', $this->getPropertySelections());
        $this->registry->set('items', $this->getItems());
        $this->registry->set('itemVariations', $this->getItemVariations());
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

    private function getSalesPrices(): SalesPriceResponse
    {
        $salesPriceRequest = new SalesPriceRequest();

        $salesPrices = [];

        foreach (Utils::sendIterableRequest($this->client, $salesPriceRequest) as $response) {
            $salesPriceResponse = SalesPriceParser::parse($response);
            $salesPrices = array_merge($salesPriceResponse->all(), $salesPrices);
        }

        return new SalesPriceResponse(1, count($salesPrices), true, $salesPrices);
    }

    private function getAttributes(): AttributeResponse
    {
        $attributeRequest = new AttributeRequest();

        $attributes = [];

        foreach (Utils::sendIterableRequest($this->client, $attributeRequest) as $response) {
            $attributeResponse = AttributeParser::parse($response);
            $attributes = array_merge($attributeResponse->all(), $attributes);
        }

        return new AttributeResponse(1, count($attributes), true, $attributes);
    }

    private function getManufacturers(): ManufacturerResponse
    {
        $manufacturerRequest = new ManufacturerRequest();

        $manufacturers = [];

        foreach (Utils::sendIterableRequest($this->client, $manufacturerRequest) as $response) {
            $manufacturerResponse = ManufacturerParser::parse($response);
            $manufacturers = array_merge($manufacturerResponse->all(), $manufacturers);
        }

        return new ManufacturerResponse(1, count($manufacturers), true, $manufacturers);
    }

    private function getProperties(): PropertyResponse
    {
        $propertyRequest = new PropertyRequest();

        $properties = [];

        foreach (Utils::sendIterableRequest($this->client, $propertyRequest) as $response) {
            $propertyResponse = PropertyParser::parse($response);
            $properties = array_merge($propertyResponse->all(), $properties);
        }

        return new PropertyResponse(1, count($properties), true, $properties);
    }

    private function getItemProperties(): ItemPropertyResponse
    {
        $propertyRequest = new ItemPropertyRequest();

        $properties = [];

        foreach (Utils::sendIterableRequest($this->client, $propertyRequest) as $response) {
            $propertyResponse = ItemPropertyParser::parse($response);
            $properties = array_merge($propertyResponse->all(), $properties);
        }

        return new ItemPropertyResponse(1, count($properties), true, $properties);
    }

    private function getUnits(): UnitResponse
    {
        $unitRequest = new UnitRequest();

        $units = [];

        foreach (Utils::sendIterableRequest($this->client, $unitRequest) as $response) {
            $unitResponse = UnitParser::parse($response);
            $units = array_merge($unitResponse->all(), $units);
        }

        return new UnitResponse(1, count($units), true, $units);
    }

    private function getPropertySelections(): PropertySelectionResponse
    {
        $selectionsRequest = new PropertySelectionRequest();

        $selections = [];

        foreach (Utils::sendIterableRequest($this->client, $selectionsRequest) as $response) {
            $selectionsResponse = PropertySelectionParser::parse($response);
            $selections = array_merge($selectionsResponse->all(), $selections);
        }

        return new PropertySelectionResponse(1, count($selections), true, $selections);
    }

    private function getItems(): ItemResponse
    {
        $itemRequest = new ItemRequest(null, $this->config->getLanguage());

        $items = [];

        foreach (Utils::sendIterableRequest($this->client, $itemRequest) as $response) {
            $itemResponse = ItemParser::parse($response);
            $items = array_merge($itemResponse->all(), $items);
        }

        return new ItemResponse(1, count($items), true, $items);
    }

    private function getItemVariations(): ItemVariationResponse
    {
        /** @var ItemResponse $items */
        $items = $this->registry->get('items');
        $itemIds = implode(',', $items->getAllIds());
        $itemVariationRequest = new ItemVariationRequest(
            $this->getRequiredVariationValues(),
            true,
            null,
            null,
            $itemIds
        );

        $itemVariations = [];

        foreach (Utils::sendIterableRequest($this->client, $itemVariationRequest) as $response) {
            $itemVariationResponse = ItemVariationParser::parse($response);
            $itemVariations = array_merge($itemVariationResponse->all(), $itemVariations);
        }

        return new ItemVariationResponse(1, count($itemVariations), true, $itemVariations);
    }

    private function getRequiredVariationValues(): string
    {
        $variationValues = [];

        $categories = $this->registry->get('categories');
        if ($categories && $categories->all()) {
            $variationValues[] = 'variationCategories';
        }

        $salesPrices = $this->registry->get('salesPrices');
        if ($salesPrices && $salesPrices->all()) {
            $variationValues[] = 'variationSalesPrices';
        }

        $attributes = $this->registry->get('attributes');
        if ($attributes && $attributes->all()) {
            $variationValues[] = 'variationAttributeValues';
        }

        $itemProperties = $this->registry->get('itemProperties');
        $properties = $this->registry->get('properties');

        if ($itemProperties && $properties) {
            $allProperties = array_merge($itemProperties->all(), $properties->all());
            if (!empty($allProperties)) {
                $variationValues[] = 'variationProperties';
            }
        }

        $units = $this->registry->get('units');
        if ($units && $units->all()) {
            $variationValues[] = 'units';
        }

        array_push(
            $variationValues,
            'variationBarcodes',
            'variationClients',
            'properties',
            'itemImages',
            'tags'
        );

        return implode(',', $variationValues);
    }
}
