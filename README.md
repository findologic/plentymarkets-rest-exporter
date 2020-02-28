# Plentymarkets REST exporter

This is a work in progress rewrite of the findologic/plentymarkets-rest-export.

### Installing

Run `composer install`.

### Running the export

When developing or just testing, running the export may be very useful.  
Before an export can be started though, you need to set the correct configuration in
`config/config.yml`. After that you can run an export with  `php bin/run_export.php`.