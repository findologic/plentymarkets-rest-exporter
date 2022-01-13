<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use FINDOLOGIC\PlentyMarketsRestExporter\Config\FindologicConfig;

trait ConfigHelper
{
    public function getDefaultConfig(array $overrides = []): FindologicConfig
    {
        return new FindologicConfig(array_merge([
            'domain' => 'plenty-testshop.de',
            'username' => 'user',
            'password' => 'pretty secure, I think!',
            'language' => 'de',
            'multiShopId' => 0,
            'availabilityId' => null,
            'priceId' => null,
            'rrpId' => null,
            'debug' => false
        ], $overrides));
    }
}
