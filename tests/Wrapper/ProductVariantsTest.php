<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\PlentyShop;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;

class ProductVariantTest extends AbstractProductTest
{
    protected function setUp(bool $useVariants = false): void
    {
        parent::setUp(true);
    }

    /**
     * @dataProvider attributesAreSetFromAllVariationsTestProvider
     */
    public function testAttributesAreSetFromAllVariations(
        array $expectedAttributeValues
    ) {
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_attribute_values.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $attributeResponse = $this->getMockResponse('AttributeResponse/response.json');
        $attributes = AttributeParser::parse($attributeResponse);
        $attributes = $attributes->all();
        array_unshift($attributes, $attributes[0]);

        $this->registryServiceMock->expects($this->exactly(3))
            ->method('getAttribute')
            ->withConsecutive([1], [2], [1])
            ->willReturnOnConsecutiveCalls(...$attributes);

        $product = $this->getProduct();
        $item = $product->processProductData();

        // TODO: check item's attributes property directly once attributes getter is implemented

        foreach ($item->getVariants() as $variant) {
            $attributesMap = $this->getMappedAttributes($variant);

            foreach ($attributesMap as $key => $attribute) {
                if (is_array($expectedAttributeValues[$key])) {
                    $this->assertContains($attributesMap[$key][0], $expectedAttributeValues[$key]);
                } else {
                    $this->assertEquals($expectedAttributeValues[$key], $attributesMap[$key][0]);
                }
            }
        }
    }

    public function testOverriddenPriceIsSetByLowestValues(): void
    {
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_lowest_price_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $this->registryServiceMock->expects($this->any())->method('getRrpId')->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();

        foreach ($item->getVariants() as $variant) {
            $overriddenPrice = $this->getArrayFirstElement($variant->getOverriddenPrice()->getValues());
            $this->assertEquals(100, $overriddenPrice);
        }
    }

    /**
     * @dataProvider cheapestVariationIsExportedTestProvider
     */
    public function testCheapestVariationIsExported(
        string $variationResponseFile,
        string $expectedImg,
        string $expectedPrice,
        string $expectedUrl
    ): void {
        $expectedPriceId = 1;
        $text = new Text([
            'lang' => 'de',
            'name1' => 'Pretty awesome name!',
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => 'Easy, transparent, sexy',
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => 'That is the best item, and I am a bit longer text.',
            'technicalData' => 'Interesting technical information.',
            'urlPath' => 'urlPath',
            'keywords' => 'keywords from product'
        ]);

        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse($variationResponseFile);
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $plentyShop = new PlentyShop([PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->registryServiceMock->method('getPriceId')->willReturn($expectedPriceId);

        $this->storeConfigurationMock->expects($this->any())
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->itemMock->expects($this->any())
            ->method('getTexts')
            ->willReturn([$text]);

        $this->storeConfigurationMock->expects($this->once())->method('getDefaultLanguage')
            ->willReturn('de');

        $product = $this->getProduct();
        $item = $product->processProductData();

        $itemPrice = $this->getArrayFirstElement($item->getPrice()->getValues());
        $itemUrl = $this->getArrayFirstElement($item->getUrl()->getValues());
        $itemImage = $this->getImages($item);
        $this->assertEquals($expectedPrice, $itemPrice);
        $this->assertEquals($expectedUrl, $itemUrl);
        $this->assertEquals($expectedImg, !empty($itemImage) ? $itemImage[0]->getUrl() : '');
    }

    public function testOrdernumbersAreSetFromAllVariations()
    {
        $expectedOrderNumbers = [
            '1', '11', '1111', '111', '11111', '111111', '2', '22', '2222', '222', '22222', '222222'
        ];
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_ordernumber_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        // TODO: check item's order numbers property directly once order numbers getter is implemented
        $orderNumbers = $this->getOrderNumbers($item);
        $this->assertEquals($expectedOrderNumbers, $orderNumbers);
    }
}
