# Plentymarkets REST exporter

[![GitHub Actions Tests](https://github.com/findologic/plentymarkets-rest-exporter-new/workflows/Tests/badge.svg)](https://github.com/findologic/plentymarkets-rest-exporter-new/actions)
[![codecov](https://codecov.io/gh/findologic/plentymarkets-rest-exporter-new/branch/master/graph/badge.svg)](https://codecov.io/gh/findologic/plentymarkets-rest-exporter-new)

## Table of Contents

1. [Synopsis](#synopsis)
1. [Requirements](#requirements)
1. [Installation](#installation)
1. [Running the export](#running-the-export)
1. [Development](#development)
   1. [Running the export](#running-the-export)
      1. [Using a shopkey to run the export](#using-a-shopkey-to-run-the-export)
      1. [Configuration](#configuration)
      1. [Debugging the export](#debugging-the-export)

## Synopsis

This is a **work in progress** rewrite of the [findologic/plentymarkets-rest-export](https://github.com/findologic/plentymarkets-rest-export).  

The Plentymarkets REST API is being called to get all necessary product data for
FINDOLOGIC. The data is then wrapped to generate a FINDOLOGIC-consumable XML/CSV file. In the case of
XML this could be multiple files.

## Requirements

* PHP >= 7.3
* PHP JSON extension
* Composer

## Installation

1. Run `composer install` to install all required dependencies.
1. Copy `config/config.dist.yml` to `config/config.yml`, and set the
 configuration for the shop, you want to export the data from.

## Running the export

1. Run `composer export` or `bin/console export:start`.
1. When the export was successful, you may be able to find the exported CSV/XML file
 in the `/export` directory.


* If you have a shopkey, see [Using a shopkey to run the export](#using-a-shopkey-to-run-the-export).
* If debug mode is true (inside `config/config.yml`), you may be able to find all requests/responses inside
 of the `/debug` directory.

If you want to debug, read more about [debugging the export](#debugging-the-export).

## Development

### Directory structure

* `.github` Contains everything related to GitHub, including GitHub Actions.
* `bin` Contains executables. E.g. [Running the export](#running-the-export).
* `config` Contains configuration files. E.g [Export configuration](#configuration).
* `debug` Contains Request/Responses after starting an export.
* `export` Contains the exported XML(s)/CSV file(s).
* `logs` Contains the log of the last export.
* `src` Contains all source code.
* `tests` Contains all unit-tests.
* `vendor` Contains source code of dependencies.

### Running tests

Running tests is as simple as it gets. Either run `composer test`,
 or use your IDE to run the tests. When running them with the IDE
 you may want to include `phpunit.xml.dist` as alternative configuration file.

#### Using a shopkey to run the export

NOTE: *Running an export with a shopkey requires the option `customerLoginUri` to be set in
`config/config.yml`!*

You can also run the export for a specific shopkey by calling the export with an
additional shopkey parameter `composer export [shopkey]` or `bin/export [shopkey]`.

Once a shopkey is supplied the configuration in `config/config.yml` may be ignored.

#### Configuration

Here is a short table that explains each configuration option.
Configuration changes can be done in `config/config.yml`.

| Configuration option | Description                                                                                                                                                                                                                                  |
|----------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| username             | The user which is used to authenticate to the REST API. It needs [these](https://docs.findologic.com/doku.php?id=integration_documentation:plentymarkets_ceres_plugin:rest_export#necessary_permissions) permissions to successfully export. |
| password             | The password of the API user.                                                                                                                                                                                                                |
| domain               | The domain of the shop without protocol (http/https). E.g. www.your-shop.com                                                                                                                                                                 |
| multiShopId          | The multiShopId of the shop. Each language has their own multiShopId at Plentymarkets. It is also known as "Webstore ID".                                                                                                                    |
| availabilityId       | Products that have this availability id assigned, won't be exported.                                                                                                                                                                         |
| priceId              | Id of the exported prices that should be shown.                                                                                                                                                                                              |
| rrpId                | Id of the recommended retail price that should be shown.                                                                                                                                                                                     |
| language             | Language of the shop. E.g. DE, EN, FR                                                                                                                                                                                                        |
| debug                | Boolean that if set to true, will log all requests/responses inside of the `debug` folder.                                                                                                                                                   |
| customerLoginUri     | Adding this to your config allows you to call the export with an additional shopkey parameter.                                                                                                                                               |

#### Debugging the export

If you want to debug the export, you can simply right-click `bin/export`
inside of the IDE and select "*Debug 'export (PHP Script)'*".

Setting the configuration option "debug" in your `config/config.yml` to `true`, will
automatically create request/response files inside of the `debug` folder.  
If you no longer need them, they can be cleared anytime running `composer clear`,
or `bin/clearExportFiles`.
