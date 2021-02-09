<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PluginConfigurationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PluginConfigurationResponseTest extends TestCase
{
    use ResponseHelper;

    public function testMethodsForEntitySearchInResponse(): void
    {
        $response = $this->getMockResponse('PluginConfigurationResponse/findologic.json');
        $pluginConfigurationResponse = PluginConfigurationParser::parse($response);

        $pluginConfig = $pluginConfigurationResponse->first();
        $this->assertEquals(459, $pluginConfig->getId());

        $pluginConfig = $pluginConfigurationResponse->findOne(['key' => 'nav_enabled']);
        $this->assertEquals(460, $pluginConfig->getId());

        $pluginConfigs = $pluginConfigurationResponse->find(['type' => 'selectBox']);
        $this->assertCount(4, $pluginConfigs);

        $pluginConfigs = $pluginConfigurationResponse->all();
        $this->assertCount(5, $pluginConfigs);
    }

    public function testDataCanBeFetched(): void
    {
        $response = $this->getMockResponse('PluginConfigurationResponse/sample.json');
        $pluginConfigurationResponse = PluginConfigurationParser::parse($response);

        $configuration = $pluginConfigurationResponse->first();

        $this->assertEquals($this->getExpectedData(), $configuration->getData());

        $this->assertEquals('461', $configuration->getId());
        $this->assertEquals('config.config1', $configuration->getKey());
        $this->assertEquals(null, $configuration->getValue());
        $this->assertEquals(44, $configuration->getPluginId());
        $this->assertEquals('Config.config1', $configuration->getLabel());
        $this->assertEquals('inputText', $configuration->getType());
        $this->assertEquals([], $configuration->getPossibleValues());
        $this->assertEquals('default value', $configuration->getDefault());
        $this->assertEquals(null, $configuration->getTab());
        $this->assertEquals(false, $configuration->getScss());
    }

    private function getExpectedData(): array
    {
        return [
            'id' => '461',
            'key' => 'config.config1',
            'value' => null,
            'plugin_id' => 44,
            'label' => 'Config.config1',
            'type' => 'inputText',
            'possibleValues' => [],
            'default' => 'default value',
            'tab' => null,
            'scss' => false,
        ];
    }
}
