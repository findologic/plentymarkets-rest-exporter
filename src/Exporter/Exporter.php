<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use Carbon\Carbon;
use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\AuthorizationException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CriticalException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\PermissionException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry\EmptyResponseException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\ThrottlingException;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Wrapper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class Exporter
{
    public const DEFAULT_LOCATION = __DIR__ . '/../../var/export';

    public const TYPE_CSV = 0;
    public const TYPE_XML = 1;

    public const SUCCESS = 0;
    public const FAILURE = 1;

    protected LoggerInterface $internalLogger;

    protected LoggerInterface $customerLogger;

    protected Config $config;

    protected Wrapper $wrapper;

    protected Client $client;

    protected Registry $registry;

    protected RegistryService $registryService;

    protected ItemRequest $itemRequest;

    protected PimVariationRequest $itemVariationRequest;

    protected ?LibflexportExporter $fileExporter;

    protected int $offset = 0;

    protected float $exportStartTime = 0;

    protected float $exportEndTime = 0;

    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $this->internalLogger = $internalLogger;
        $this->customerLogger = $customerLogger;
        $this->config = $config;
        $this->client = $client ??
            new Client($this->getDefaultGuzzleClient(), $config, $internalLogger, $customerLogger);
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
        PimVariationResponse $variations,
        ?PropertySelectionResponse $propertySelection = null
    ): void;

    /**
     * Builds a new exporter instance. Use Exporter::TYPE_CSV / Exporter::TYPE_XML to get the respective type.
     *
     * @param Config $config
     * @param LoggerInterface $internalLogger
     * @param LoggerInterface $customerLogger
     * @param string|null $exportPath
     * @param string|null $fileNamePrefix
     * @param Client|null $client
     * @param RegistryService|null $registryService
     * @param ItemRequest|null $itemRequest
     * @param PimVariationRequest|null $pimVariationRequest
     * @param LibflexportExporter|null $fileExporter
     * @return Exporter
     */
    public static function buildInstance(
        Config $config,
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        ?string $exportPath = null,
        ?string $fileNamePrefix = null,
        ?Client $client = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ): Exporter {
        $usedPath = $exportPath ?? Utils::env('EXPORT_DIR', self::DEFAULT_LOCATION);

        return new XmlExporter(
            $internalLogger,
            $customerLogger,
            $config,
            $usedPath,
            $fileNamePrefix,
            $client,
            $registryService,
            $itemRequest,
            $pimVariationRequest,
            $fileExporter
        );
    }

    /**
     * @throws Throwable
     * @return int The exit code. 0 is only returned in case of success. Any other code may indicate a failure.
     *
     * @see Exporter::SUCCESS
     * @see Exporter::FAILURE
     */
    public function export(): int
    {
        try {
            $this->exportStartTime = microtime(true);
            $this->registryService->warmUp();
            $this->exportProducts();
            $this->exportEndTime = microtime(true);

            return self::SUCCESS;
        } catch (Throwable $e) {
            // Running the command locally or for tests, it is easier for debugging to simply throw the error.
            if (Utils::env('APP_ENV') === 'dev' || Utils::env('APP_ENV') === 'test') {
                throw $e;
            }

            $this->customerLogger->error('An unexpected error occurred. Export will stop.');
            $this->internalLogger->error(
                sprintf(
                    'An unexpected error occurred. Export will stop. %s: %s. Stack trace: %s',
                    get_class($e),
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                ['exception' => $e]
            );

            return self::FAILURE;
        }
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

    /**
     * @throws EmptyResponseException
     * @throws PermissionException
     * @throws CustomerException
     * @throws GuzzleException
     * @throws AuthorizationException
     * @throws ThrottlingException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CriticalException
     */
    protected function exportProducts(): void
    {
        $propertySelection = $this->registryService->getPropertySelections();
        $page = 1;

        do {
            $products = $this->getItems($page);
            $variations = $products->getAllIds() ? $this->getItemVariations($products->getAllIds()) : [];
            $this->wrapData(count($products->all()), $products, $variations, $propertySelection);
            $this->offset += $this->itemRequest->getItemsPerPage();

            $page++;
        } while (!$products->isLastPage());
    }

    /**
     * @throws EmptyResponseException
     * @throws PermissionException
     * @throws CustomerException
     * @throws GuzzleException
     * @throws ThrottlingException
     * @throws AuthorizationException
     * @throws CriticalException
     */
    private function getItems($page): ItemResponse
    {
        $this->itemRequest->setPage($page);
        $this->itemRequest->setItemsPerPage($this->config->getItemsPerPage());
        $response = $this->client->send($this->itemRequest);

        return ItemParser::parse($response);
    }

    /**
     * @throws PermissionException
     * @throws EmptyResponseException
     * @throws CustomerException
     * @throws GuzzleException
     * @throws ThrottlingException
     * @throws AuthorizationException
     * @throws CriticalException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getItemVariations(array $itemIds): PimVariationResponse
    {
        $variations = [];

        $this->itemVariationRequest
            ->setPage(1)
            ->setParam('itemIds', $itemIds)
            ->setParam('isActive', true)
            ->setParam('sortBy', 'itemId_asc')
            ->setParam('clientId', $this->registryService->getWebStore()->getStoreIdentifier())
            ->setWith($this->getRequiredVariationValues());

        foreach (Utils::sendIterableRequest($this->client, $this->itemVariationRequest, $this->config) as $response) {
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

    private function getDefaultGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'verify' => false,
        ]);
    }
}
