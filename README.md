# Plentymarkets REST exporter

[![GitHub Actions Tests](https://github.com/findologic/plentymarkets-rest-exporter-new/workflows/Tests/badge.svg)](https://github.com/findologic/plentymarkets-rest-exporter-new/actions)

## Table of Contents

1. [Synopsis](#synopsis)
1. [Requirements](#requirements)
1. [Installation](#installation)
1. [Development](#development)
   1. [Running the export](#running-the-export)
      1. [Debugging](#debugging)

## Synopsis

This is a work in progress rewrite of the findologic/plentymarkets-rest-export.

The Plentymarkets REST API is being called to get all necessary product data for
FINDOLOGIC. The data is then wrapped to generate either a CSV or an XML file.

## Requirements

* PHP >= 7.3
* PHP JSON extension

## Installation

Simply run `composer install`.

## Development

### Running the export

1. Copy `config/config.dist.yml` to `config/config.yml`.
1. Set all necessary configurations in `config/config.yml`.
1. Run `composer export`.

Alternatively you can run the command manually:  `php bin/run_export.php | sleep 1 && tail -f logs/import.log`.
  * `php bin/run_export.php` Starts the export
  * `sleep 1 && tail -f -n +1 logs/import.log` Waits one second, so the last log is cleared and then
  follows the log, so you can see the current status. Arguments `-n +1` tells `tail` to follow from the beginning.

#### Debugging

If you want to debug the export, you can simply right-click `bin/run_export.php`
inside of the IDE and select "*Debug 'run_export.php (PHP Script)'*".
