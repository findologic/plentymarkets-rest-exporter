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
}
