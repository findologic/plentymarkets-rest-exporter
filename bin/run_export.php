<?php

declare(strict_types=1);

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use Log4Php\Configurators\LoggerConfigurationAdapterXML;
use Log4Php\Logger;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

const IMPORT_LOG_PATH = __DIR__ . '/../logs/import.log';

// Empty log before each new import.
if (file_exists(IMPORT_LOG_PATH)) {
    file_put_contents(IMPORT_LOG_PATH, '');
}

require_once __DIR__ . '/../vendor/autoload.php';

$configurationAdapter = new LoggerConfigurationAdapterXML();
$configuration = $configurationAdapter->convert(__DIR__ . '/../config/logger.xml');
$configuration['appenders']['default']['params']['file'] = IMPORT_LOG_PATH;

Logger::configure($configuration);

$internalLogger = Logger::getLogger('import.php');
$customerLogger = Logger::getLogger('import.php');
$configDest = __DIR__ . '/../config/config.yml';

try {
    $rawConfig = Yaml::parseFile($configDest);
} catch (ParseException $e) {
    $internalLogger->error('There was an error while parsing the configuration: ' . $e->getMessage());
    exit(1);
}

$config = new Config($rawConfig);

/** @var XmlExporter $exporter */
$exporter = Exporter::buildInstance(Exporter::TYPE_CSV, $config, $internalLogger, $customerLogger);
try {
    $exporter->export();
} catch (Throwable $e) {
    $internalLogger->error($e->getMessage());
    $internalLogger->trace($e->getTraceAsString());
    $customerLogger->error('The export was unsuccessful and failed with an exception.');
    exit(1);
}

$customerLogger->info('The export was successful!');
