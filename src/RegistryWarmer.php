<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

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
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class RegistryWarmer
{
    protected LoggerInterface $internalLogger;

    protected LoggerInterface $customerLogger;

    protected Config $config;

    protected Client $client;

    protected Registry $registry;

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

    public function warmUpRegistry(): void
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
}