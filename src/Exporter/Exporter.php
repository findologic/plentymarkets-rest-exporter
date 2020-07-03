<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Wrapper;
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

    /** @var Wrapper */
    protected $wrapper;

    /** @var Client */
    protected $client;

    /** @var Registry */
    protected $registry;

    /** @var RegistryService */
    protected $registryService;

    /** @var ItemRequest */
    protected $itemRequest;

    /** @var ItemVariationRequest */
    protected $itemVariationRequest;

    /** @var LibflexportExporter */
    protected $fileExporter;

    /** @var int */
    protected $offset = 0;

    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?ItemVariationRequest $itemVariationRequest = null,
        LibflexportExporter $fileExporter = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = $client ?? new Client(new GuzzleClient(), $config, $internalLogger, $customerLogger);
        $this->registry = $registry ?? new Registry();
        if (!$registryService) {
            $registryService = new RegistryService(
                $internalLogger,
                $customerLogger,
                $config,
                $this->client,
                $this->registry
            );
        }
        $this->registryService = $registryService;
        $this->itemRequest = $itemRequest ?: new ItemRequest(null, $this->config->getLanguage());
        $this->itemVariationRequest = $itemVariationRequest ?: new ItemVariationRequest();
        $this->fileExporter = $fileExporter;
    }

    abstract protected function wrapData(
        int $totalCount,
        ItemResponse $products,
        ItemVariationResponse $variations
    ): void;

    /**
     * Builds a new exporter instance. Use Exporter::TYPE_CSV / Exporter::TYPE_XML to get the respective type.
     *
     * @param int $type Exporter::TYPE_CSV / Exporter::TYPE_XML
     * @param Config $config
     * @param LoggerInterface $internalLogger
     * @param LoggerInterface $customerLogger
     * @param Client|null $client
     * @param Registry|null $registry
     * @param RegistryService|null $registryService
     * @param ItemRequest|null $itemRequest
     * @param ItemVariationRequest|null $itemVariationRequest
     * @param LibflexportExporter|null $fileExporter
     * @param string|null $exportPath
     * @return Exporter
     */
    public static function buildInstance(
        int $type,
        Config $config,
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        ?Client $client = null,
        ?Registry $registry = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?ItemVariationRequest $itemVariationRequest = null,
        ?LibflexportExporter $fileExporter = null,
        ?string $exportPath = __DIR__ . '/../../export'
    ): Exporter {
        switch ($type) {
            case self::TYPE_CSV:
                return new CsvExporter(
                    $internalLogger,
                    $customerLogger,
                    $config,
                    $exportPath,
                    $client,
                    $registry,
                    $registryService,
                    $itemRequest,
                    $itemVariationRequest,
                    $fileExporter
                );
            case self::TYPE_XML:
                return new XmlExporter(
                    $internalLogger,
                    $customerLogger,
                    $config,
                    $client,
                    $registry,
                    $registryService,
                    $itemRequest,
                    $itemVariationRequest,
                    $fileExporter
                );
            default:
                throw new InvalidArgumentException('Unknown or unsupported exporter type.');
        }
    }

    public function export(): void
    {
        $this->registryService->warmUp();
        $this->exportProducts();
    }

    protected function exportProducts(): void
    {
        $page = 1;
        while (true) {
            $products = $this->getItems($page);
            $variations = $products->getAllIds() ? $this->getItemVariations($products->getAllIds()) : [];
            $this->wrapData(count($products->all()), $products, $variations);
            $this->offset += ItemVariationRequest::$ITEMS_PER_PAGE;

            if ($products->isLastPage()) {
                break;
            }
            // @codeCoverageIgnoreStart
            $page++;
            // @codeCoverageIgnoreEnd
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

        $this->itemVariationRequest
            ->setPage(1)
            ->setItemId($itemIds)
            ->setWith($this->getRequiredVariationValues())
            ->setIsActive(true);

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
