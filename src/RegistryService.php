<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Definition\PropertyOptionType;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\PermissionException;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PluginConfigurationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PluginsFromSetParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\AttributeRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\CategoryRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemPropertyRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ManufacturerRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PluginConfigurationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PluginFromSetRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemPropertyGroupRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertyGroupRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertyRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PropertySelectionRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\SalesPriceRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\UnitRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\VatRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\StandardVatRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\WebStoreResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

class RegistryService
{
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

    public function warmUp(): void
    {
        $this->customerLogger->info('Starting to initialise necessary data (categories, attributes, etc.).');

        $this->fetchWebStores();
        $this->fetchCategories();
        $this->fetchVat();
        $this->fetchSalesPrices();
        $this->fetchAttributes();
        $this->fetchManufacturers();
        $this->fetchProperties();
        $this->fetchPropertyGroups();
        $this->fetchItemProperties();
        $this->fetchUnits();
        $this->fetchPropertySelections();
        $this->fetchItemPropertyGroups();
        $this->fetchPluginConfigurations();
    }

    public function getWebStore(): WebStore
    {
        /** @var WebStore $webStore */
        $webStore = $this->get('webStore');

        return $webStore;
    }

    public function getAllWebStores(): WebStoreResponse
    {
        /** @var WebStoreResponse $allWebStores */
        $allWebStores = $this->get('allWebStores');

        return $allWebStores;
    }

    public function getCategory(int $id): ?Category
    {
        /** @var Category $category */
        $category = $this->get(sprintf('category_%d', $id));

        return $category;
    }

    public function getAttribute(int $id): ?Attribute
    {
        /** @var Attribute $attribute */
        $attribute = $this->get(sprintf('attribute_%d', $id));

        return $attribute;
    }

    public function getVat(int $id): VatConfiguration
    {
        /** @var VatConfiguration $vat */
        $vat = $this->get(sprintf('vat_%d', $id));

        return $vat;
    }

    public function getStandardVat(): VatConfiguration
    {
        /** @var VatConfiguration $vat */
        $vat = $this->get('standardVat');

        return $vat;
    }

    public function getSalesPrice(int $id): SalesPrice
    {
        /** @var SalesPrice $salesPrice */
        $salesPrice = $this->get(sprintf('salesPrice_%d', $id));

        return $salesPrice;
    }

    public function getManufacturer(int $id): Manufacturer
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = $this->get(sprintf('manufacturer_%d', $id));

        return $manufacturer;
    }

    public function getProperty(int $id): ?Property
    {
        /** @var Property $property */
        $property = $this->get(sprintf('property_%d', $id));

        return $property;
    }

    public function getPropertyGroup(int $id): ?PropertyGroup
    {
        /** @var PropertyGroup $propertyGroup */
        $propertyGroup = $this->get(sprintf('propertyGroup_%d', $id));

        return $propertyGroup;
    }

    public function getItemProperty(int $id): ?ItemProperty
    {
        /** @var ItemProperty $itemProperty */
        $itemProperty = $this->get(sprintf('itemProperty_%d', $id));

        return $itemProperty;
    }

    public function getUnit(int $id): ?Unit
    {
        /** @var Unit $unit */
        $unit = $this->get(sprintf('unit_%d', $id));

        return $unit;
    }

    public function getPropertySelections(): ?PropertySelectionResponse
    {
        /** @var PropertySelectionResponse $propertySelection */
        $propertySelection = $this->get('propertySelections');

        return $propertySelection;
    }

    public function getItemPropertyGroup(int $id): ?ItemPropertyGroup
    {
        /** @var ItemPropertyGroup $propertyGroup */
        $propertyGroup = $this->get(sprintf('itemPropertyGroup_%d', $id));

        return $propertyGroup;
    }

    public function getPriceId(): int
    {
        /** @var SalesPrice $defaultSalesPrice */
        $defaultSalesPrice = $this->get('defaultPrice');

        return $this->config->getPriceId() ?? $defaultSalesPrice->getId();
    }

    public function getRrpId(): ?int
    {
        /** @var SalesPrice $defaultRrpId */
        $defaultRrpId = $this->get('defaultRrpId');

        if ($rrpId = $this->config->getRrpId()) {
            return $rrpId;
        }

        return $defaultRrpId ? $defaultRrpId->getId() : null;
    }

    public function getPluginConfigurations($pluginName = null): array
    {
        $configs = $this->get('pluginConfigurations');

        if (!$pluginName) {
            return $configs;
        }

        return $configs[$pluginName] ?? [];
    }

    protected function fetchWebStores(): void
    {
        $webStoreRequest = new WebStoreRequest();
        $response = $this->client->send($webStoreRequest);

        $webStores = WebStoreParser::parse($response);
        $this->set('allWebStores', $webStores);

        $webStore = $webStores->findOne([
            'id' => $this->config->getMultiShopId()
        ]);

        if (!$webStore) {
            throw new CustomerException(sprintf(
                'Could not find a web store with the multishop id "%d"',
                $this->config->getMultiShopId()
            ));
        }

        $this->set('webStore', $webStore);
    }

    protected function fetchCategories(): void
    {
        /** @var WebStore $webStore */
        $webStore = $this->get('webStore');

        $categoryRequest = new CategoryRequest($webStore->getStoreIdentifier());

        foreach (Utils::sendIterableRequest($this->client, $categoryRequest) as $response) {
            $categoryResponse = CategoryParser::parse($response);
            $categoriesMatchingCriteria = $categoryResponse->find([
                'details' => [
                    'lang' => strtoupper($this->config->getLanguage()),
                    'plentyId' => $webStore->getStoreIdentifier()
                ]
            ]);

            foreach ($categoriesMatchingCriteria as $category) {
                $this->set('category_' . $category->getId(), $category);
            }
        }
    }

    protected function fetchVat(): void
    {
        $vatRequest = new VatRequest();
        foreach (Utils::sendIterableRequest($this->client, $vatRequest) as $response) {
            $vatResponse = VatParser::parse($response);

            foreach ($vatResponse->all() as $vat) {
                $this->set('vat_' . $vat->getId(), $vat);
            }
        }

        $standardVatRequest = new StandardVatRequest($this->getWebStore()->getId());
        $standardVatResponse = $this->client->send($standardVatRequest);
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);

        $this->set('standardVat', $standardVat);
    }

    protected function fetchSalesPrices(): void
    {
        $salesPriceRequest = new SalesPriceRequest();
        foreach (Utils::sendIterableRequest($this->client, $salesPriceRequest) as $response) {
            $salesPriceResponse = SalesPriceParser::parse($response);

            foreach ($salesPriceResponse->all() as $salesPrice) {
                switch ($salesPrice->getType()) {
                    case 'default':
                        $this->set('defaultPrice', $salesPrice);
                        break;
                    case 'rrp':
                        $this->set('defaultRrpPrice', $salesPrice);
                        break;
                    default:
                        break;
                }

                $this->set('salesPrice_' . $salesPrice->getId(), $salesPrice);
            }
        }
    }

    protected function fetchAttributes(): void
    {
        $attributeRequest = new AttributeRequest();
        foreach (Utils::sendIterableRequest($this->client, $attributeRequest) as $response) {
            $attributeResponse = AttributeParser::parse($response);

            foreach ($attributeResponse->all() as $attribute) {
                $this->set('attribute_' . $attribute->getId(), $attribute);
            }
        }
    }

    protected function fetchManufacturers(): void
    {
        $manufacturerRequest = new ManufacturerRequest();
        foreach (Utils::sendIterableRequest($this->client, $manufacturerRequest) as $response) {
            $manufacturerResponse = ManufacturerParser::parse($response);

            foreach ($manufacturerResponse->all() as $manufacturer) {
                $this->set('manufacturer_' . $manufacturer->getId(), $manufacturer);
            }
        }
    }

    protected function fetchProperties(): void
    {
        $propertyRequest = new PropertyRequest();

        foreach (Utils::sendIterableRequest($this->client, $propertyRequest) as $response) {
            $propertyResponse = PropertyParser::parse($response);

            foreach ($propertyResponse->all() as $property) {
                if (!$this->isPropertyExportable($property)) {
                    $property->setSkipExport(true);
                }
                $this->set('property_' . $property->getId(), $property);
            }
        }
    }

    protected function fetchPropertyGroups(): void
    {
        try {
            $propertyGroupRequest = new PropertyGroupRequest();

            foreach (Utils::sendIterableRequest($this->client, $propertyGroupRequest) as $response) {
                $propertyGroupResponse = PropertyGroupParser::parse($response);

                foreach ($propertyGroupResponse->all() as $propertyGroup) {
                    $this->set('propertyGroup_' . $propertyGroup->getId(), $propertyGroup);
                }
            }
        } catch (PermissionException $e) {
            $this->customerLogger->warning(
                'Required permission \'Setup > Property > Group > Show\' has not been granted. ' .
                'This may cause some properties not to be exported!'
            );
        }
    }

    protected function fetchItemProperties(): void
    {
        $propertyRequest = new ItemPropertyRequest();

        foreach (Utils::sendIterableRequest($this->client, $propertyRequest) as $response) {
            $propertyResponse = ItemPropertyParser::parse($response);

            foreach ($propertyResponse->all() as $property) {
                $this->set('itemProperty_' . $property->getId(), $property);
            }
        }
    }

    protected function fetchUnits(): void
    {
        $unitRequest = new UnitRequest();
        foreach (Utils::sendIterableRequest($this->client, $unitRequest) as $response) {
            $unitResponse = UnitParser::parse($response);

            foreach ($unitResponse->all() as $unit) {
                $this->set('unit_' . $unit->getId(), $unit);
            }
        }
    }

    protected function fetchPropertySelections(): void
    {
        try {
            $selectionsRequest = new PropertySelectionRequest();

            $selections = [];
            foreach (Utils::sendIterableRequest($this->client, $selectionsRequest) as $response) {
                $selectionsResponse = PropertySelectionParser::parse($response);
                $selections = array_merge($selectionsResponse->all(), $selections);
            }

            $this->set(
                'propertySelections',
                new PropertySelectionResponse(1, count($selections), true, $selections)
            );
        } catch (PermissionException $e) {
            $this->customerLogger->warning(
                'Required permission \'Setup > Property > Selection > Show\' has not been granted. ' .
                'This causes selection and multiSelect properties not to be exported!'
            );
        }
    }

    protected function fetchItemPropertyGroups(): void
    {
        $propertyGroupRequest = new ItemPropertyGroupRequest('names');

        foreach (Utils::sendIterableRequest($this->client, $propertyGroupRequest) as $response) {
            $propertyGroupResponse = ItemPropertyGroupParser::parse($response);

            foreach ($propertyGroupResponse->all() as $propertyGroup) {
                $this->set('itemPropertyGroup_' . $propertyGroup->getId(), $propertyGroup);
            }
        }
    }

    protected function fetchPluginConfigurations(): void
    {
        $allConfigurations = [];

        $pluginSetId = $this->getWebStore()->getPluginSetId();

        $pluginSetRequest = new PluginFromSetRequest($pluginSetId);
        $response = $this->client->send($pluginSetRequest);

        $plugins = PluginsFromSetParser::parse($response);

        foreach ($plugins->all() as $plugin) {
            $pluginConfigurationRequest = new PluginConfigurationRequest($plugin->getId(), $pluginSetId);

            try {
                $response = $this->client->send($pluginConfigurationRequest);
            } catch (PermissionException $e) {
                $this->customerLogger->error(
                    'Required permissions \'Plugins > Configurations > Show\' have not been granted. ' .
                    'Product-URLs will be exported in Callisto format!'
                );

                $this->set('pluginConfigurations', $allConfigurations);

                return;
            }

            $configurations = PluginConfigurationParser::parse($response);

            foreach ($configurations->all() as $configuration) {
                $value = $configuration->getValue();

                if ($value === null) {
                    $value = $configuration->getDefault();
                }

                $allConfigurations[$plugin->getName()][$configuration->getKey()] = $value;
            }
        }

        $this->set('pluginConfigurations', $allConfigurations);
    }

    private function isPropertyExportable(Property $property): bool
    {
        $referrerId = $this->config->getExportReferrerId();

        if ($referrerId === null) {
            return true;
        }

        foreach ($property->getOptions() as $option) {
            if ($option->getType() !== PropertyOptionType::REFERRERS) {
                continue;
            }

            if (is_numeric($option->getValue()) && ((float)$option->getValue() === $referrerId)) {
                return true;
            }
        }

        return false;
    }

    private function set(string $key, $data)
    {
        $shop = md5($this->config->getDomain());

        $this->registry->set($shop . '_' . $key, $data);
    }

    private function get(string $key)
    {
        $shop = md5($this->config->getDomain());

        return $this->registry->get($shop . '_' . $key);
    }
}
