<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\PlentyShop;
use PHPUnit\Framework\TestCase;

class PlentyShopTest extends TestCase
{
    /**
     * @dataProvider shouldUseCallistoUrlTestProvider
     */
    public function testShouldUseLegacyCallistoUrl(array $configData, bool $expectedResult): void
    {
        $plentyShop = new PlentyShop($configData);
        $this->assertEquals($expectedResult, $plentyShop->shouldUseLegacyCallistoUrl());
    }

    public function shouldUseCallistoUrlTestProvider(): array
    {
        return [
            'unknown config' => [
                'config' => [
                    'global.test' => false
                ],
                'shouldUseCallistoUrl' => true
            ],
            'with enable old url pattern config set to false' => [
                'config' => [
                    PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false
                ],
                'shouldUseCallistoUrl' => false
            ],
            'with enable old url pattern config set to true' => [
                'config' => [
                    PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => true
                ],
                'shouldUseCallistoUrl' => true
            ],
        ];
    }

    /**
     * @dataProvider shouldExportGroupableAttributeVariantsSeparatelyProvider
     */
    public function testShouldExportGroupableAttributeVariantsSeparately(array $configData, bool $expectedResult)
    {
        $plentyShop = new PlentyShop($configData);
        $this->assertEquals($expectedResult, $plentyShop->shouldExportGroupableAttributeVariantsSeparately());
    }

    public function shouldExportGroupableAttributeVariantsSeparatelyProvider(): array
    {
        return [
            'unknown config' => [
                'config' => [
                    'global.test' => false
                ],
                'shouldExportGroupableAttributeVariantsSeparately' => true
            ],
            'with variation show type config set to unknown value' => [
                'config' => [
                    PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => 'unknown'
                ],
                'shouldExportGroupableAttributeVariantsSeparately' => false
            ],
            'with variation show type config set to all' => [
                'config' => [
                    PlentyShop::KEY_ITEM_VARIATION_SHOW_TYPE => PlentyShop::VARIANT_MODE_ALL
                ],
                'shouldExportGroupableAttributeVariantsSeparately' => true
            ],
        ];
    }
}
