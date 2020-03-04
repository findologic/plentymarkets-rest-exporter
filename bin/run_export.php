<?php

declare(strict_types=1);

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use Log4Php\Configurators\LoggerConfigurationAdapterXML;
use Log4Php\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

const IMPORT_LOG_PATH = __DIR__ . '/../logs/import.log';

$configurationAdapter = new LoggerConfigurationAdapterXML();
Logger::configure($configurationAdapter->convert(__DIR__ . '/../config/logger.xml'));

// Empty log before each new import.
if (file_exists(IMPORT_LOG_PATH)) {
    file_put_contents(IMPORT_LOG_PATH, '');
}

$internalLogger = Logger::getLogger('import.php');
$customerLogger = Logger::getLogger('import.php');

$rawConfig = yaml_parse_file(__DIR__ . '/../config/config.yml');
$config = new Config($rawConfig);

/** @var XmlExporter $exporter */
$exporter = Exporter::buildInstance(Exporter::TYPE_XML, $config, $internalLogger, $customerLogger);

$exporter->export();
