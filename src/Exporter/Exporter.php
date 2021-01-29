<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use Carbon\Carbon;
use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Wrapper;
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class Exporter
{
    public const DEFAULT_LOCATION = __DIR__ . '/../../var/export';

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

    /** @var PimVariationRequest */
    protected $itemVariationRequest;

    /** @var LibflexportExporter */
    protected $fileExporter;

    /** @var int */
    protected $offset = 0;

    /** @var float */
    protected $exportStartTime = 0;

    /** @var float */
    protected $exportEndTime = 0;

    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        LibflexportExporter $fileExporter = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = $client ?? new Client(new GuzzleClient(), $config, $internalLogger, $customerLogger);
        if (!$registryService) {
            $registryService = new RegistryService(
                $internalLogger,
                $customerLogger,
                $config,
                $this->client,
                new Registry()
            );
        }
        $this->registryService = $registryService;
        $this->itemRequest = $itemRequest ?: new ItemRequest(null, $this->config->getLanguage());
        $this->itemVariationRequest = $pimVariationRequest ?: new PimVariationRequest();
        $this->fileExporter = $fileExporter;
    }

    abstract protected function wrapData(
        int $totalCount,
        ItemResponse $products,
        PimVariationResponse $variations
    ): void;

    /**
     * Builds a new exporter instance. Use Exporter::TYPE_CSV / Exporter::TYPE_XML to get the respective type.
     *
     * @param int $type Exporter::TYPE_CSV / Exporter::TYPE_XML
     * @param Config $config
     * @param LoggerInterface $internalLogger
     * @param LoggerInterface $customerLogger
     * @param Client|null $client
     * @param RegistryService|null $registryService
     * @param ItemRequest|null $itemRequest
     * @param PimVariationRequest|null $pimVariationRequest
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
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null,
        ?string $exportPath = null
    ): Exporter {
        $usedPath = $exportPath ?? Utils::env('EXPORT_DIR', self::DEFAULT_LOCATION);

        switch ($type) {
            case self::TYPE_CSV:
                return new CsvExporter(
                    $internalLogger,
                    $customerLogger,
                    $config,
                    $usedPath,
                    $client,
                    $registryService,
                    $itemRequest,
                    $pimVariationRequest,
                    $fileExporter
                );
            case self::TYPE_XML:
                return new XmlExporter(
                    $internalLogger,
                    $customerLogger,
                    $config,
                    $client,
                    $registryService,
                    $itemRequest,
                    $pimVariationRequest,
                    $fileExporter
                );
            default:
                throw new InvalidArgumentException('Unknown or unsupported exporter type.');
        }
    }

    public function export(): void
    {
        $this->exportStartTime = microtime(true);
        $this->registryService->warmUp();
        $this->exportProducts();
        $this->exportEndTime = microtime(true);
    }

    public function getExportTime(): string
    {
        $start = Carbon::createFromTimestamp($this->exportStartTime);
        $end = Carbon::createFromTimestamp($this->exportEndTime);

        return $end->diff($start)->format('%H:%I:%S');
    }

    public function getWrapper(): Wrapper
    {
        return $this->wrapper;
    }

    protected function exportProducts(): void
    {
        $page = 1;

        do {
            $products = $this->getItems($page);
            $variations = $products->getAllIds() ? $this->getItemVariations($products->getAllIds()) : [];
            $this->wrapData(count($products->all()), $products, $variations);
            $this->offset += ItemVariationRequest::$ITEMS_PER_PAGE;

            $page++;
        } while (!$products->isLastPage());
    }

    private function getItems($page): ItemResponse
    {
        $this->itemRequest->setPage($page);
        $response = $this->client->send($this->itemRequest);

        return ItemParser::parse($response);
    }

    private function getItemVariations(array $itemIds): PimVariationResponse
    {
        $variations = [];

        $this->itemVariationRequest
            ->setPage(1)
            ->setParam('itemIds', $itemIds)
            ->setParam('isActive', true)
            ->setParam('clientId', $this->registryService->getWebStore()->getStoreIdentifier())
            ->setWith($this->getRequiredVariationValues());

        foreach (Utils::sendIterableRequest($this->client, $this->itemVariationRequest) as $response) {
            $pimVariationResponse = PimVariationsParser::parse($response);
            $variations = array_merge($pimVariationResponse->all(), $variations);
        }

        return new PimVariationResponse(1, count($variations), true, $variations);
    }

    private function getRequiredVariationValues(): array
    {
        return [
            'attributeValues',
            'attributeValues.attributeValue',
            'barcodes',
            'base.characteristics',
            'base.images',
            'base.item',
            'categories',
            'clients',
            'images.image',
            'properties',
            'properties.property',
            'salesPrices',
            'tags.tag',
            'unit'
        ];
    }
}
