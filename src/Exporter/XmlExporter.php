<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use Psr\Log\LoggerInterface;
use FINDOLOGIC\Export\Enums\ExporterType;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\ItemsWrapper;

class XmlExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        string $exportPath,
        ?string $fileNamePrefix,
        ?Client $client = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $internalLogger->debug('Using Plentymarkets XmlExporter for exporting.');
        if (!$fileExporter) {
            $fileExporter = LibflexportExporter::create(ExporterType::XML, 100);
        }

        parent::__construct(
            $internalLogger,
            $customerLogger,
            $config,
            $client,
            $registryService,
            $itemRequest,
            $pimVariationRequest,
            $fileExporter
        );

        $this->wrapper = new ItemsWrapper(
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
     * @codeCoverageIgnore
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
