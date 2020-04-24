<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\Export\Exporter as LibflexportExporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Product;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\CsvWrapper;
use Psr\Log\LoggerInterface;

class CsvExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null
    ) {
        $internalLogger->debug('Using Plentymarkets CsvExporter for exporting.');

        parent::__construct($internalLogger, $customerLogger, $config, $client, $registry);
    }

    /**
     * @param Product[] $products
     */
    protected function wrapData(int $totalCount, array $products, array $variations): void
    {
        $exporter = LibflexportExporter::create(LibflexportExporter::TYPE_CSV, 100);

        $wrapper = new CsvWrapper(
            $this->exportPath,
            $exporter,
            $this->config,
            $this->registry
        );

        $wrapper->wrap($this->offset, $totalCount, $products, $variations);
    }
}
