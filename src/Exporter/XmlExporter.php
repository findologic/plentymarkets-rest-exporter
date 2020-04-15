<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Exporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use Psr\Log\LoggerInterface;

class XmlExporter extends Exporter
{
    public function __construct(
        LoggerInterface $internalLogger,
        LoggerInterface $customerLogger,
        Config $config,
        ?Client $client = null,
        ?Registry $registry = null
    ) {
        $internalLogger->debug('Using Plentymarkets XmlExporter for exporting.');

        parent::__construct($internalLogger, $customerLogger, $config, $client, $registry);
    }
}
