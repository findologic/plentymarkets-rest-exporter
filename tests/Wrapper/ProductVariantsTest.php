<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use ReflectionClass;
use FINDOLOGIC\Export\Data\Attribute;
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
        $expectedOverridenPrices = [150, 100, 100];
        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_lowest_price_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $this->registryServiceMock->expects($this->any())->method('getRrpId')->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();

        foreach ($item->getVariants() as $key => $variant) {
            $overriddenPrice = $this->getArrayFirstElement($variant->getOverriddenPrice()->getValues());
            $this->assertEquals($expectedOverridenPrices[$key], $overriddenPrice);
        }
    }

    /**
     * @dataProvider cheapestVariationIsExportedTestProvider
     */
    public function testCheapestVariationIsExported(
        string $variationResponseFile,
        string $expectedImg,
        string $expectedPrice
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

        $this->storeConfigurationMock->method('getDefaultLanguage')
            ->willReturn('de');

        $product = $this->getProduct();
        $item = $product->processProductData();

        $itemPrice = $this->getArrayFirstElement($item->getPrice()->getValues());
        $itemUrl = $this->getArrayFirstElement($item->getUrl()->getValues());
        $itemImage = $this->getImages($item);
        $this->assertEquals($expectedPrice, $itemPrice);
        // $this->assertEquals($expectedUrl, $itemUrl);
        $this->assertEquals($expectedImg, !empty($itemImage) ? $itemImage[0]->getUrl() : '');
    }

    public function testOrdernumbersAreSetFromAllVariations()
    {
        $expectedOrderNumbers = [
            ['1', '11', '1111', '111', '11111', '111111'],
            ['2', '22', '2222', '222', '22222', '222222']
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

        foreach ($item->getVariants() as $key => $variant) {
            $orderNumbers = $this->getOrderNumbers($variant);
            $this->assertEquals($expectedOrderNumbers[$key], $orderNumbers);
        }
    }

    /**
     * @dataProvider orderNumberExportConfigurationTestProvider
     */
    public function testOrderNumbersAreExportedAccordingToConfiguration(
        array $orderNumbersExportConfig,
        array $expectedOrderNumbers
    ): void {
        $this->config = $this->getDefaultConfig($orderNumbersExportConfig);
        $this->config->setUseVariants(true);
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_ordernumber_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $splittedOrderNumbers = [[], []];

        array_walk($expectedOrderNumbers, function ($orderNumber) use (&$splittedOrderNumbers) {
            str_contains($orderNumber, '1')
                ? $splittedOrderNumbers[0][] = $orderNumber
                : $splittedOrderNumbers[1][] = $orderNumber;
        });

        foreach ($item->getVariants() as $key => $variant) {
            $orderNumbers = $this->getOrderNumbers($variant);
            $this->assertEquals($splittedOrderNumbers[$key], $orderNumbers);
        }
    }

    /**
     * @dataProvider exportFreeFieldsConfigurationTestProvider
     */
    public function testFreeTextFieldsAreNotExportedAccordingToConfiguration(
        array $exportFreeFieldsConfig,
        array $expectedAttributeValues
    ): void {
        $this->config = $this->getDefaultConfig($exportFreeFieldsConfig);
        $this->config->setUseVariants(true);
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_free_fields_exporting_test.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $this->itemMock = $this->getItem($variations->first()->getBase()->getItem()->getData());

        $this->variationEntityMocks[] = $variations->first();
        $product = $this->getProduct();
        $item = $product->processProductData();

        foreach ($item->getVariants() as $key => $variant) {
            $attributesMap = $this->getMappedAttributes($variant);

            foreach ($expectedAttributeValues as $key => $expectedAttributeValue) {
                if ($key === 'free1') {
                    continue;
                }
                $this->assertEquals($expectedAttributeValue, $attributesMap[$key][0]);
            }
        }
    }

    public function testDimensionsWithoutValueAreIgnored(): void
    {
        $expectedExportedAttributes = [
            [
                new Attribute('cat', ['Sessel & Hocker']),
                new Attribute('cat_url', ['/wohnzimmer/sessel-hocker/']),
                new Attribute('dimensions_height_mm', ['300']),
                new Attribute('dimensions_length_mm', ['200']),
                new Attribute('dimensions_width_mm', ['100']),
                new Attribute('dimensions_weight_g', ['2000']),
                new Attribute('dimensions_weight_net_g', ['1000']),
            ],
            [
                new Attribute('cat', ['Sessel & Hocker']),
                new Attribute('cat_url', ['/wohnzimmer/sessel-hocker/']),
                new Attribute('dimensions_height_mm', ['400']),
                new Attribute('dimensions_length_mm', ['300']),
                new Attribute('dimensions_width_mm', ['200']),
                new Attribute('dimensions_weight_g', ['4000']),
                new Attribute('dimensions_weight_net_g', ['2000']),
            ]

        ];

        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variants_with_dimensions.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = array_slice($variations->all(), 0, 2);

        $product = $this->getProduct();
        $item = $product->processProductData();

        foreach ($item->getVariants() as $key => $variant) {
            $reflector = new ReflectionClass($variant);
            $attributes = $reflector->getProperty('attributes');
            $attributeValues = $attributes->getValue($variant);

            $this->assertEqualsCanonicalizing($expectedExportedAttributes[$key], $attributeValues);
        }
    }
}
