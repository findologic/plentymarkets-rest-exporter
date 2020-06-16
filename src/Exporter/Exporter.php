<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryWarmer;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class Exporter
{
    public const
        TYPE_CSV = 0,
        TYPE_XML = 1;

    protected LoggerInterface $internalLogger;

    protected LoggerInterface $customerLogger;

    protected Config $config;

    protected Client $client;

    protected Registry $registry;

    protected RegistryWarmer $registryWarmer;

    protected string $exportPath = __DIR__ . '/../../export';

    protected ItemRequest $itemRequest;

    protected ItemVariationRequest $itemVariationRequest;

    protected int $offset = 0;

    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null,
        ?RegistryWarmer $registryWarmer = null,
        ?ItemRequest $itemRequest = null,
        ?ItemVariationRequest $itemVariationRequest = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = $client ?? new Client(new GuzzleClient(), $config, $internalLogger, $customerLogger);
        $this->registry = $registry ?? new Registry();
        if (!$registryWarmer) {
            $registryWarmer = new RegistryWarmer($internalLogger, $customerLogger, $config, $client, $this->registry);
        }
        $this->registryWarmer = $registryWarmer;
        if (!$itemRequest) {
            $itemRequest = new ItemRequest(null, $this->config->getLanguage());
        }
        $this->itemRequest = $itemRequest;
        if (!$itemVariationRequest) {
            $itemVariationRequest = new ItemVariationRequest();
            $itemVariationRequest->setWith($this->getRequiredVariationValues())->setIsActive(true);
        }
        $this->itemVariationRequest = $itemVariationRequest;
    }

    abstract protected function wrapData(int $totalCount, array $products, array $variations): void;

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
        $this->registryWarmer->warmUpRegistry();
        $this->exportProducts();
    }

    public function setExportPath(string $path): self
    {
        $this->exportPath = $path;

        return $this;
    }

    public function getExportPath(): string
    {
        return $this->exportPath;
    }

    protected function exportProducts(): void
    {
        $page = 1;
        while (true) {
            $products = $this->getItems($page);
            $variations = $products->getAllIds() ? $this->getItemVariations($products->getAllIds()) : [];
            $this->wrapData(count($products->all()), $products->all(), $variations->all());
            $this->offset += ItemVariationRequest::$ITEMS_PER_PAGE;

            if ($products->isLastPage()) {
                break;
            }

            $page++;
        }
    }

    private function getItems($page): ItemResponse
    {
        $this->itemRequest->setPage($page);
        $response = $this->client->send($this->itemRequest);

        return ItemParser::parse($response);
    }

    private function getItemVariations(array $itemIds): ItemVariationResponse
    {
        $itemVariations = [];

        $this->itemVariationRequest->setPage(1)->setItemId($itemIds);

        foreach (Utils::sendIterableRequest($this->client, $this->itemVariationRequest) as $response) {
            $itemVariationResponse = ItemVariationParser::parse($response);
            $itemVariations = array_merge($itemVariationResponse->all(), $itemVariations);
        }

        return new ItemVariationResponse(1, count($itemVariations), true, $itemVariations);
    }

    private function getRequiredVariationValues(): array
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

        return $variationValues;
    }
}
