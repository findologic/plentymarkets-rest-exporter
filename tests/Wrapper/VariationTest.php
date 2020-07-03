<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Variation as VariationWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{
    use ResponseHelper;
    use ConfigHelper;

    /** @var Registry|MockObject */
    private $registryMock;

    /** @var Config */
    private $defaultConfig;

    public function setUp(): void
    {
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $categoryResponse = $this->getMockResponse('CategoryResponse/response.json');
        $categories = CategoryParser::parse($categoryResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);

        $this->registryMock->method('get')->willReturnOnConsecutiveCalls($categories, $attributes);

        $this->defaultConfig = $this->getDefaultConfig();
        $this->defaultConfig->setLanguage('en');
    }

    public function testVariationWrap(): void
    {
        $itemVariationResponse = $this->getMockResponse('ItemVariationResponse/response.json');
        $variationEntities = ItemVariationParser::parse($itemVariationResponse);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertEquals(true, $wrapper->isMain());
        $this->assertEquals(0, $wrapper->getPosition());
        $this->assertEquals(16, $wrapper->getVatId());
        $this->assertEquals('S-000813-C', $wrapper->getNumber());
        $this->assertEquals('modeeeel', $wrapper->getModel());
        $this->assertEquals(1001, $wrapper->getId());
        $this->assertEquals(103, $wrapper->getItemId());
        $this->assertEquals(['3213213213213'], $wrapper->getBarcodes());
        $this->assertEquals([499.0, 560.0], $wrapper->getPrices());

        $attributes = $wrapper->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertEquals('cat', $attributes[0]->getKey());
        $this->assertEquals(['Armchairs & Stools'], $attributes[0]->getValues());
        $this->assertEquals('cat_url', $attributes[1]->getKey());
        $this->assertEquals(['https://plentydemoshop.com/wohnzimmer/sessel-hocker/'], $attributes[1]->getValues());
        $this->assertEquals('Couch color', $attributes[2]->getKey());
        $this->assertEquals(['purple'], $attributes[2]->getValues());

        $properties = $wrapper->getProperties();
        $this->assertCount(1, $properties);
        $this->assertEquals('price_id', $properties[0]->getKey());
        $this->assertEquals(['' => '1'], $properties[0]->getAllValues());
    }
}
