<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigCanBeGetAndSet(): void
    {
        $expectedDomain = 'blubbergurken.io';
        $expectedUsername = 'findo';
        $expectedPassword = 'VerySecure!1234';
        $expectedLanguage = 'DE';
        $expectedMultiShopId = 555;
        $expectedAvailabilityId = 85;
        $expectedPriceId = 999;
        $expectedRrpId = 2637;
        $expectedDebug = true;

        $config = new Config([
            'domain' => $expectedDomain,
            'username' => $expectedUsername,
            'password' => $expectedPassword,
            'language' => $expectedLanguage,
            'multiShopId' => $expectedMultiShopId,
            'availabilityId' => $expectedAvailabilityId,
            'priceId' => $expectedPriceId,
            'rrpId' => $expectedRrpId,
            'debug' => $expectedDebug,
            'non-existent-config-option' => 'should not fail'
        ]);

        $this->assertSame($expectedDomain, $config->getDomain());
        $this->assertSame($expectedUsername, $config->getUsername());
        $this->assertSame($expectedPassword, $config->getPassword());
        $this->assertSame($expectedLanguage, $config->getLanguage());
        $this->assertSame($expectedMultiShopId, $config->getMultiShopId());
        $this->assertSame($expectedAvailabilityId, $config->getAvailabilityId());
        $this->assertSame($expectedPriceId, $config->getPriceId());
        $this->assertSame($expectedRrpId, $config->getRrpId());
        $this->assertSame($expectedDebug, $config->isDebug());
    }

    public function testDataCanBeFetchedFromCustomerLoginData(): void
    {
        $expectedDomain = 'plenty-testshop.de';
        $expectedUsername = 'FL_API';
        $expectedPassword = 'pretty secure, I think..';
        $expectedLanguage = 'de';
        $expectedMultiShopId = 0;
        $expectedAvailabilityId = null;
        $expectedPriceId = 1;
        $expectedRrpId = 2;
        $expectedUnavailableVariations = false;

        $customerLoginResponse = [
            '1234' => [
                'id' => 1234,
                'url' => $expectedDomain,
                'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD',
                'shoptype' => 'plentyMarkets',
                'export_username' => $expectedUsername,
                'export_password' => $expectedPassword,
                'language' => $expectedLanguage,
                'plentymarkets' => [
                    'multishop_id' => $expectedMultiShopId,
                    'availability_id' => $expectedAvailabilityId,
                    'price_id' => $expectedPriceId,
                    'rrp_id' => $expectedRrpId,
                    'exporter' => 'REST',
                    'exportUnavailableVariations' => $expectedUnavailableVariations
                ],
            ]
        ];

        $config = Config::parseByCustomerLoginResponse($customerLoginResponse);

        $this->assertSame($expectedDomain, $config->getDomain());
        $this->assertSame($expectedUsername, $config->getUsername());
        $this->assertSame($expectedPassword, $config->getPassword());
        $this->assertSame($expectedLanguage, $config->getLanguage());
        $this->assertSame($expectedAvailabilityId, $config->getAvailabilityId());
        $this->assertSame($expectedPriceId, $config->getPriceId());
        $this->assertSame($expectedRrpId, $config->getRrpId());
        $this->assertSame($expectedUnavailableVariations, $config->isExportUnavailableVariations());
    }

    public function testConfigCanBeParsedFromArray()
    {
        $expectedDomain = 'findologic.plentymarkets-x1.com';
        $expectedUsername = 'findologic';
        $expectedPassword = 'SecurePassword123';
        $expectedLanguage = 'de';
        $expectedAvailabilityId = 5;
        $expectedPriceId = 1;
        $expectedRrpId = 7;
        $expectedUnavailableVariations = false;

        $config = Config::fromArray($this->getConfigArray());

        $this->assertSame($expectedDomain, $config->getDomain());
        $this->assertSame($expectedUsername, $config->getUsername());
        $this->assertSame($expectedPassword, $config->getPassword());
        $this->assertSame($expectedLanguage, $config->getLanguage());
        $this->assertSame($expectedAvailabilityId, $config->getAvailabilityId());
        $this->assertSame($expectedPriceId, $config->getPriceId());
        $this->assertSame($expectedRrpId, $config->getRrpId());
        $this->assertSame($expectedUnavailableVariations, $config->isExportUnavailableVariations());
    }

    private function getConfigArray()
    {
        return [
            'id' => 3167,
            'cid' => 2637,
            'url' => 'findologic.plentymarkets-x1.com',
            'shopkey' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
            'shoptype' => 'plentyMarkets',
            'server' => 32,
            'autocomplete' => 5,
            'status' => 1,
            'account_type' => [
                '__isInitialized__' => true,
            ],
            'backend_server' => 'some.backend.findologic.com',
            'fallback_server' => 'some.fallback.findologic.com',
            'status_comment' => 'Nice comment',
            'export_url' => null,
            'csv_url' => null,
            'export_username' => 'findologic',
            'export_password' => 'SecurePassword123',
            'csv_url_valid' => false,
            'export_url_valid' => false,
            'frontend_type' => 7,
            'db_revision' => 1,
            'import_successful' => true,
            'template_file' => 'html3.1.tpl',
            'selector_template_file' => 'plenty-selector.tpl',
            'language_id' => 2,
            'selector' => false,
            'current_step' => 1,
            'hierarchical_categories' => true,
            'category_delimiter' => '_',
            'max_filters' => 1000,
            'max_prive_filters' => 4,
            'items_per_page' => 25,
            'paginator_pages_before' => 3,
            'paginator_pages_after' => 3,
            'encoding' => 'UNICODE',
            'script_file' => 'index.php',
            'min_relevance' => 0,
            'created_at' => [
                'date' => '2016-08-05 00:00:00',
                'timezone_type' => 3,
                'timezone' => 'Europe/Berlin',
            ],
            'is_template' => false,
            'encoding_autocomplete' => '0',
            'min_relevance_relative' => 0.01,
            'with_logo' => true,
            'autocomplete_items' => 10,
            'auto_import' => true,
            'use_custom_exporter' => false,
            'auto_import_minute' => '10',
            'auto_import_hour' => '2',
            'start_time' => 1618359000,
            'paid_until' => [
                'date' => '2016-02-28 00:00:00',
                'timezone_type' => 3,
                'timezone' => 'Europe/Berlin',
            ],
            'last_product_notification' => null,
            'last_query_notification' => null,
            'paket' => 0,
            'filter_base_url' => null,
            'filter_image_extension' => null,
            'tariff_id' => null,
            'default_order' => 'salesfrequency dynamic desc',
            'export_limit' => null,
            'configuration' => "withcategoryinautocomplete;true\nwithcompanyinautocomplete;true",
            'notification_url' => null,
            'ticket_id' => null,
            'last_statechange' => null,
            'error_comment' => null,
            'interrupt_notifications_until' => null,
            'name' => 'plentyMarkets',
            'email' => 'plugins@findologic.com',
            'language' => 'de',
            'importer_type' => 'csv',
            'self_learning_vocabulary' => false,
            'vendor_attribute_name' => 'vendor',
            'network_timeout_seconds' => 600,
            'prediction_model' => null,
            'prediction_features' => null,
            'use_variants' => false,
            'plentymarkets' => [
                'multishop_id' => 0,
                'availability_id' => 5,
                'export_unavailable_variants' => false,
                'price_id' => 1,
                'rrp_id' => 7,
                'exporter' => 'REST',
            ],
            'shopify' => [
                'name' => null,
                'token' => null,
            ],
            'synonyms' => "fully;enduro bike Trek\n\nLaptop;Notebook\nParadeiser;Tomate\nSackerl;Tüte",
            'bonus' => '{}',
            'product_placements' => '{}',
            'search_concepts' => '{}',
            'dictionary_items' => [
                'add' => [
                ],
                'remove' => [
                ],
            ],
        ];
    }
}
