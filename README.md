# Plentymarkets REST exporter

![GitHub Actions Tests](https://github.com/thekeymaster/plentymarkets-rest-exporter-new/workflows/Tests/badge.svg)

## Table of Contents

1. [Synopsis](#synopsis)
1. [Requirements](#requirements)
1. [Installation](#installation)
1. [Development](#development)
   1. [Running the export](#running-the-export)

## Synopsis

This is a work in progress rewrite of the findologic/plentymarkets-rest-export.

The Plentymarkets REST API is being called to get all necessary product data for
FINDOLOGIC. The data is then wrapped to generate either a CSV or an XML file.

## Requirements

* PHP >= 7.3
* PHP JSON extension
* PHP YAML extension

## Installation

Simply run `composer install`.

## Development

### Running the export

1. Copy `config/config.dist.yml` to `config/config.yml`.
1. Set all necessary configurations in `config/config.yml`.
1. Run `php bin/run_export.php`.

If you want to debug the export, you can simply right-click `bin/run_export.php`
inside of the IDE and select "*Debug 'run_export.php (PHP Script)'*".
