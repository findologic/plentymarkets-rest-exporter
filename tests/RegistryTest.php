<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\WebStoreResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    public function testSettingAndGettingDataFromRegistryWorksAsExpected(): void
    {
        $expectedWebStoreResponse = new WebStoreResponse([
            new WebStore([
                'id' => 1234,
                'type' => 'plentyMarkets',
                'storeIdentifier' => 5678,
                'name' => 'Blubbergurken Store',
                'pluginSetId' => 83,
                'configuration' => []
            ])
        ]);

        $registry = new Registry();
        $registry->set('webstore', $expectedWebStoreResponse);
        $this->assertSame($registry->get('webstore'), $expectedWebStoreResponse);

        $this->assertNull($registry->get('non-existent'));
    }
}
