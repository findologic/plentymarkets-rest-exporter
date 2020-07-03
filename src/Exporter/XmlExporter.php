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
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;
use Psr\Log\LoggerInterface;

class XmlExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null,
        ?RegistryService $registryService = null,
        ?ItemRequest $itemRequest = null,
        ?ItemVariationRequest $itemVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $internalLogger->debug('Using Plentymarkets XmlExporter for exporting.');

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
    }

    /**
     * @codeCoverageIgnore
     */
    protected function wrapData(int $totalCount, ItemResponse $products, ItemVariationResponse $variations): void
    {
        // TODO: Implement wrapData() method.
    }
}
