<?php

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Exporter\XmlExporter;
use Log4Php\Configurators\LoggerConfigurationAdapterXML;
use Log4Php\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$configurationAdapter = new LoggerConfigurationAdapterXML();
Logger::configure($configurationAdapter->convert(__DIR__ . '/../config/logger.xml'));

$internalLogger = Logger::getLogger('import.php');
$customerLogger = Logger::getLogger('import.php');

$rawConfig = yaml_parse_file(__DIR__ . '/../config/config.yml');

// Change the config to the config that the customer has.
$config = new Config($rawConfig);

/** @var XmlExporter $exporter */
$exporter = Exporter::buildInstance(Exporter::TYPE_XML, $config, $internalLogger, $customerLogger);

$exporter->export();
