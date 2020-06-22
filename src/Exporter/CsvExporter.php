<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\CsvWrapper;
use Psr\Log\LoggerInterface;

class CsvExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        string $exportPath,
        ?Client $client = null,
        ?Registry $registry = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?ItemVariationRequest $itemVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $internalLogger->debug('Using Plentymarkets CsvExporter for exporting.');

        if (!$fileExporter) {
            $fileExporter = LibflexportExporter::create(LibflexportExporter::TYPE_CSV, 100);
        }

        parent::__construct(
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

        $this->wrapper = new CsvWrapper(
            $exportPath,
            $this->fileExporter,
            $this->config,
            $this->registry
        );
    }

    /**
     * @param Item[] $products
     */
    protected function wrapData(int $totalCount, array $products, array $variations): void
    {
        $this->wrapper->wrap($this->offset, $totalCount, $products, $variations);
    }
}
