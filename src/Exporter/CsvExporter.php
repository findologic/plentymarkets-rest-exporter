<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use Log4Php\Logger;

class CsvExporter extends Exporter
{
    public function __construct(
        Logger $internalLogger,
        Logger $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null
    ) {
        $internalLogger->debug('Using Plentymarkets CsvExporter for exporting.');

        parent::__construct($internalLogger, $customerLogger, $config, $client, $registry);
    }
}
