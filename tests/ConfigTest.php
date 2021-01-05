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
                    'exporter' => 'REST'
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
    }
}
