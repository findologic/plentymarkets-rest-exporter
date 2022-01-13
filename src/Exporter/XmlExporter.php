<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config\FindologicConfig;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use Psr\Log\LoggerInterface;

class XmlExporter extends Exporter
{
    public function __construct(
        LoggerInterface      $internalLogger,
        LoggerInterface      $customerLogger,
        FindologicConfig     $config,
        ?Client              $client = null,
        ?RegistryService     $registryService = null,
        ?ItemRequest         $itemRequest = null,
        ?PimVariationRequest $pimVariationRequest = null,
        ?LibflexportExporter $fileExporter = null
    ) {
        $internalLogger->debug('Using Plentymarkets XmlExporter for exporting.');

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
    }

    /**
     * @codeCoverageIgnore
     */
    protected function wrapData(int $totalCount, ItemResponse $products, PimVariationResponse $variations): void
    {
        // TODO: Implement wrapData() method.
    }
}
