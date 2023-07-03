<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;

trait ConfigHelper
{
    public function getDefaultConfig(array $overrides = []): Config
    {
        return new Config(array_merge([
            'domain' => 'plenty-testshop.de',
            'username' => 'user',
            'password' => 'pretty secure, I think!',
            'language' => 'de',
            'multiShopId' => 0,
            'availabilityId' => null,
            'priceId' => null,
            'rrpId' => null,
            'debug' => false,
            'useVariants' => false
        ], $overrides));
    }
}
