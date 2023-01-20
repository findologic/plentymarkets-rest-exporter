<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use Exception;
use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\CsvWrapper;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CsvExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        string $exportPath,
        ?string $fileNamePrefix,
        ?Client $client = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $pimRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $internalLogger->debug('Using Plentymarkets CsvExporter for exporting.');

        if (!$fileExporter) {
            $properties = ['price_id', 'variation_id', 'base_unit', 'package_size'];
            $fileExporter = LibflexportExporter::create(LibflexportExporter::TYPE_CSV, 100, $properties);
        }

        parent::__construct(
            $internalLogger,
            $customerLogger,
            $config,
            $client,
            $registryService,
            $pimRequest,
            $pimVariationRequest,
            $fileExporter
        );

        $this->wrapper = new CsvWrapper(
            $exportPath,
            $fileNamePrefix,
            $this->fileExporter,
            $this->config,
            $this->registryService,
            $this->internalLogger,
            $this->customerLogger
        );
    }

    /**
     * @param int $totalCount
     * @param ItemResponse $products
     * @param PimVariationResponse $variations
     * @param PropertySelectionResponse|null $propertySelection
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function wrapData(
        int $totalCount,
        ItemResponse $products,
        PimVariationResponse $variations,
        ?PropertySelectionResponse $propertySelection = null
    ): void {
        $this->wrapper->wrap($this->offset, $totalCount, $products, $variations, $propertySelection);
    }
}
