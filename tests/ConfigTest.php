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
            'useVariants' => false,
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
        $expectedReferrerId = 5.05;
        $expectedOrderNumberProductId = false;
        $expectedOrderNumberVariantId = false;
        $expectedOrderNumberVariantNumber = false;
        $expectedOrderNumberVariantModel = false;
        $expectedOrderNumberVariantBarcodes = false;
        $expectedExportFreeTextFields = false;
        $expectedDimensionUnit = 'm';
        $expectedWeightUnit = 'kg';
        $expectedUseVariants = false;
        $expectedItemsPerPage = 100;

        $accountResponse = [
            '1234' => [
                'id' => 1234,
                'url' => $expectedDomain,
                'shopkey' => 'ABCDABCDABCDABCDABCDABCDABCDABCD',
                'shoptype' => 'plentyMarkets',
                'export_username' => $expectedUsername,
                'export_password' => $expectedPassword,
                'language' => $expectedLanguage,
                'use_variants' => $expectedUseVariants,
                'plentymarkets' => [
                    'multishop_id' => $expectedMultiShopId,
                    'availability_id' => $expectedAvailabilityId,
                    'price_id' => $expectedPriceId,
                    'rrp_id' => $expectedRrpId,
                    'export_unavailable_variants' => $expectedUnavailableVariations,
                    'export_referrer_id' => $expectedReferrerId,
                    'export_ordernumber_product_id' => $expectedOrderNumberProductId,
                    'export_ordernumber_variant_id' => $expectedOrderNumberVariantId,
                    'export_ordernumber_variant_number' => $expectedOrderNumberVariantNumber,
                    'export_ordernumber_variant_model' => $expectedOrderNumberVariantModel,
                    'export_ordernumber_variant_barcodes' => $expectedOrderNumberVariantBarcodes,
                    'export_free_text_fields' => $expectedExportFreeTextFields,
                    'export_dimension_unit' => $expectedDimensionUnit,
                    'export_weight_unit' => $expectedWeightUnit,
                    'items_per_page' => $expectedItemsPerPage
                ],
            ]
        ];

        $config = Config::fromArray($accountResponse);

        $this->assertSame($expectedDomain, $config->getDomain());
        $this->assertSame($expectedUsername, $config->getUsername());
        $this->assertSame($expectedPassword, $config->getPassword());
        $this->assertSame($expectedLanguage, $config->getLanguage());
        $this->assertSame($expectedAvailabilityId, $config->getAvailabilityId());
        $this->assertSame($expectedPriceId, $config->getPriceId());
        $this->assertSame($expectedRrpId, $config->getRrpId());
        $this->assertSame($expectedUnavailableVariations, $config->isExportUnavailableVariations());
        $this->assertSame($expectedReferrerId, $config->getExportReferrerId());
        $this->assertSame($expectedOrderNumberProductId, $config->getExportOrdernumberProductId());
        $this->assertSame($expectedOrderNumberVariantId, $config->getExportOrdernumberVariantId());
        $this->assertSame($expectedOrderNumberVariantNumber, $config->getExportOrdernumberVariantNumber());
        $this->assertSame($expectedOrderNumberVariantModel, $config->getExportOrdernumberVariantModel());
        $this->assertSame($expectedOrderNumberVariantBarcodes, $config->getExportOrdernumberVariantBarcodes());
        $this->assertSame($expectedExportFreeTextFields, $config->getExportFreeTextFields());
        $this->assertSame($expectedDimensionUnit, $config->getExportDimensionUnit());
        $this->assertSame($expectedWeightUnit, $config->getExportWeightUnit());
        $this->assertSame($expectedUseVariants, $config->getUseVariants());
        $this->assertSame($expectedItemsPerPage, $config->getItemsPerPage());
    }

    /**
     * @dataProvider exportReferrerIdProvider
     */
    public function testExportReferrerIdConfigIsCastedCorrectly($rawConfigValue, $expectedCastConfigValue): void
    {
        $config = new Config(['exportReferrerId' => $rawConfigValue]);

        $this->assertSame($expectedCastConfigValue, $config->getExportReferrerId());
    }

    public function testExportFreeTextFieldsConfigDefaultValueIsSetCorrectly(): void
    {
        $config = new Config();
        $expectedResult = true;

        $this->assertSame($expectedResult, $config->getExportFreeTextFields());
    }

    public function exportReferrerIdProvider(): array
    {
        return [
            'casts int to float' => [
                'rawConfigValue' => 10,
                'expectedCastConfigValue' => 10.0
            ],
            'casts int string to float' => [
                'rawConfigValue' => '10',
                'expectedCastConfigValue' => 10.0
            ],
            'casts float string to float' => [
                'rawConfigValue' => '10.00',
                'expectedCastConfigValue' => 10.0
            ],
            'casts non-numeric string to null' => [
                'rawConfigValue' => '10.0a',
                'expectedCastConfigValue' => null
            ],
            'leaves null as null' => [
                'rawConfigValue' => null,
                'expectedCastConfigValue' => null
            ]
        ];
    }
}
