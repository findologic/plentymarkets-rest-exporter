<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use DateTime;
use ReflectionClass;
use DateTimeInterface;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\PlentyShop;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;

class ProductTest extends AbstractProductTest
{
    protected function setUp(bool $useVariants = false): void
    {
        parent::setUp($useVariants);
    }

    public function testProductWithAllVariationsMatchingConfigurationAvailabilityAreExportedIfConfigured()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $expectedImage = 'https://cdn03.plentymarkets.com/0pb05rir4h9r/' .
            'item/images/131/middle/131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg';
        $expectedOrderNumbers = ['S-000813-C', 'modeeeel', '1004', '106', '3213213213213', '101', '1005', '107'];
        $this->config->setAvailabilityId(5);
        $this->config->setExportUnavailableVariations(true);

        $rawVariations = $this->getMockResponse('Pim/Variations/variations_with_5_for_availability_id.json');
        $variations = PimVariationsParser::parse($rawVariations);
        $this->variationEntityMocks = $variations->all();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNotNull($item);

        $orderNumbers = $this->getOrderNumbers($item);
        $this->assertEquals($expectedOrderNumbers, $orderNumbers);
        $images = $this->getImages($item);
        $this->assertSame($expectedImage, $images[0]->getUrl());
    }

    public function testMatchingAvailabilityExportSettingDoesNotOverrideOtherVariationExportabilityChecks()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $this->config->setAvailabilityId(5);
        $this->config->setExportUnavailableVariations(true);

        $rawVariations = $this->getMockResponse(
            'Pim/Variations/variations_with_5_for_availability_id_and_mixed_status.json'
        );
        $variations = PimVariationsParser::parse($rawVariations);
        $this->variationEntityMocks = $variations->all();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNotNull($item);

        $orderNumbers = $this->getOrderNumbers($item);

        $this->assertEquals(['101', '1005', '107'], $orderNumbers);
    }

    public function testProductIsSuccessfullyWrapped(): void
    {
        $expectedName = 'Pretty awesome name!';
        $expectedSummary = 'Easy, transparent, sexy';
        $expectedDescription = 'That is the best item, and I am a bit longer text.';
        $urlPath = 'awesome-url-path/somewhere-in-the-store/öäü';
        $expectedUrlPath = 'awesome-url-path/somewhere-in-the-store/%C3%B6%C3%A4%C3%BC';
        $expectedPriceId = 11;
        $expectedMainVariationId = 1004;
        $expectedBaseUnit = 'Stück';
        $expectedPackageSize = '1000';

        $this->exporterMock = $this->getExporter();

        $rawVariation = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($rawVariation);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);

        $rawCategories = $this->getMockResponse('CategoryResponse/one.json');
        $categories = CategoryParser::parse($rawCategories);

        $rawManufacturers = $this->getMockResponse('ManufacturerResponse/one.json');
        $manufacturers = ManufacturerParser::parse($rawManufacturers);

        $rawUnits = $this->getMockResponse('UnitResponse/one.json');
        $units = UnitParser::parse($rawUnits);

        $this->storeConfigurationMock->expects($this->exactly(2))
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->storeConfigurationMock->expects($this->once())->method('getDefaultLanguage')
            ->willReturn('de');

        $this->registryServiceMock->expects($this->once())->method('getAllWebStores')->willReturn($webStores);
        $this->registryServiceMock->expects($this->once())->method('getCategory')
            ->willReturn($categories->first());

        $this->registryServiceMock->expects($this->once())
            ->method('getManufacturer')
            ->willReturn($manufacturers->first());

        $this->registryServiceMock->expects($this->once())
            ->method('getUnit')
            ->willReturn($units->first());

        $plentyShop = new PlentyShop([PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $this->registryServiceMock->method('getPriceId')->willReturn($expectedPriceId);

        $text = new Text([
            'lang' => 'de',
            'name1' => $expectedName,
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => $expectedSummary,
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => $expectedDescription,
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $urlPath,
            'keywords' => 'get me out',
        ]);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);

        $this->itemMock->expects($this->once())
            ->method('getManufacturerId')
            ->willReturn(1);

        $this->itemMock->method('getId')->willReturn(10);
        $this->itemMock->method('getMainVariationId')->willReturn($expectedMainVariationId);

        $this->variationEntityMocks[] = $variations->findOne(['id' => 1004]);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertSame($expectedName, $item->getName()->getValues()['']);
        $this->assertSame($expectedSummary, $item->getSummary()->getValues()['']);
        $this->assertSame($expectedDescription, $item->getDescription()->getValues()['']);
        $this->assertSame(
            'https://plenty-testshop.de/' . $expectedUrlPath . '_10_1004',
            $item->getUrl()->getValues()['']
        );

        $itemProperties = $this->getArrayFirstElement($item->getProperties());

        $this->assertSame((string)$expectedPriceId, $itemProperties['price_id']);
        $this->assertSame((string)$expectedMainVariationId, $itemProperties['variation_id']);
        $this->assertSame($expectedBaseUnit, $itemProperties['base_unit']);
        $this->assertSame($expectedPackageSize, $itemProperties['package_size']);

        $this->assertTrue(
            DateTime::createFromFormat(DateTimeInterface::ATOM, $item->getDateAdded()->getValues()['']) !== false
        );
    }

    public function testLanguagePrefixIsAddedToUrlInCaseLanguageIsAvailableButNotDefaultLanguage(): void
    {
        $expectedUrlPath = 'awesome-url-path/somewhere-in-the-store';
        $expectedLanguagePrefix = 'de';

        $this->exporterMock = $this->getExporter();

        $rawVariation = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($rawVariation);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);

        $this->storeConfigurationMock->expects($this->exactly(2))
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->storeConfigurationMock->expects($this->once())->method('getDefaultLanguage')
            ->willReturn('en');
        $this->storeConfigurationMock->expects($this->once())->method('getLanguageList')
            ->willReturn(['de', 'en']);

        $this->registryServiceMock->expects($this->once())->method('getAllWebStores')->willReturn($webStores);

        $plentyShop = new PlentyShop([PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $text = new Text([
            'lang' => $expectedLanguagePrefix,
            'name1' => 'Pretty awesome name!',
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => 'Easy, transparent, sexy',
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => 'That is the best item, and I am a bit longer text.',
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $expectedUrlPath,
            'keywords' => 'get me out',
        ]);

        $anotherText = new Text([
            'lang' => 'en',
            'name1' => 'Pretty awesome name!',
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => 'Easy, transparent, sexy',
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => 'That is the best item, and I am a bit longer text.',
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $expectedUrlPath,
            'keywords' => 'get me out',
        ]);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text, $anotherText]);

        $this->variationEntityMocks[] = $variations->first();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertSame(
            'https://plenty-testshop.de/' . $expectedLanguagePrefix . '/' . $expectedUrlPath . '_0_1004',
            $item->getUrl()->getValues()['']
        );
    }

    public function testSortIsSetByTheMainVariation(): void
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_sort_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals(['' => 2], $item->getSort()->getValues());
    }

    public function testKeywordsAreSetFromAllVariations(): void
    {
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_tags.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

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

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);

        $webStoreMock = $this->getMockBuilder(WebStore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webStoreMock->method('getStoreIdentifier')->willReturn(12345);
        $this->registryServiceMock->method('getWebStore')->willReturn($webStoreMock);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $this->storeConfigurationMock->expects($this->any())
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->storeConfigurationMock->expects($this->any())
            ->method('getDefaultLanguage')
            ->willReturn('de');

        $product = $this->getProduct();
        $item = $product->processProductData();

        $itemKeywords = $this->getItemKeywords($item);

        $this->assertEquals(['de tag 1', 'de tag 2', 'de tag 3', 'keywords from product'], $itemKeywords);
    }

    public function testPriceAndOverriddenPriceIsSetByLowestValues(): void
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
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
        $overriddenPrice = $this->getArrayFirstElement($item->getOverriddenPrice()->getValues());

        $this->assertEquals(['' => 50], $item->getPrice()->getValues());
        $this->assertEquals(100, $overriddenPrice);
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

    public function testGroupsAreSetFromAllVariations()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_different_clients.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $itemGroups = $this->getItemGroups($item);
        $this->assertEquals(['0_', '1_'], $itemGroups);
    }

    public function testOrdernumbersAreSetFromAllVariations()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
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

        $orderNumbers = $this->getOrderNumbers($item);
        $this->assertEquals($expectedOrderNumbers, $orderNumbers);
    }

    /**
     * @dataProvider orderNumberExportConfigurationTestProvider
     */
    public function testOrderNumbersAreExportedAccordingToConfiguration(
        array $orderNumbersExportConfig,
        array $expectedOrderNumbers
    ): void {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->config = $this->getDefaultConfig($orderNumbersExportConfig);
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_ordernumber_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        if (count($expectedOrderNumbers)) {
            $orderNumbers = $this->getOrderNumbers($item);
            $this->assertEquals($expectedOrderNumbers, $orderNumbers);
        } else {
            $this->assertNull($item);
            $this->assertEquals('Product is missing at least one required field: ordernumber.', $product->getReason());
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
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_free_fields_exporting_test.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $this->itemMock = $this->getItem(array_merge(
            $variations->first()->getBase()->getItem()->getData(),
            [
                'texts' => [self::DEFAULT_TEXTS],
            ]
        ));

        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();

        $this->variationEntityMocks[] = $variations->first();
        $product = $this->getProduct();
        $item = $product->processProductData();

        $attributesMap = $this->getMappedAttributes($item);

        foreach ($expectedAttributeValues as $key => $expectedAttributeValue) {
            $this->assertEquals($expectedAttributeValue, $attributesMap[$key][0]);
        }
    }

    public function testTooLongFreeTextFieldsAreIgnored(): void
    {
        $expectedAttributeValues = [
            'cat' => 'Sessel & Hocker',
            'cat_url' => '/wohnzimmer/sessel-hocker/',
            'free1' => '0000000000'
        ];

        $this->config = $this->getDefaultConfig(['exportFreeTextFields' => true]);
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/too_long_field_not_exported_test.json');
        $variations = PimVariationsParser::parse($variationResponse);

        $this->itemMock = $this->getItem(array_merge(
            $variations->first()->getBase()->getItem()->getData(),
            [
                'texts' => [self::DEFAULT_TEXTS],
            ]
        ));

        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();

        $this->variationEntityMocks[] = $variations->first();
        $product = $this->getProduct();
        $item = $product->processProductData();

        $attributesMap = $this->getMappedAttributes($item);

        foreach ($expectedAttributeValues as $key => $expectedAttributeValue) {
            $this->assertEquals($expectedAttributeValue, $attributesMap[$key][0]);
        }
    }

    /**
     * @dataProvider attributesAreSetFromAllVariationsTestProvider
     */
    public function testAttributesAreSetFromAllVariations(
        array $expectedAttributeValues
    ) {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
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

        $attributesMap = $this->getMappedAttributes($item);

        foreach ($expectedAttributeValues as $key => $expectedAttributeValue) {
            if (is_array($expectedAttributeValue)) {
                $this->assertEquals($expectedAttributeValue, $attributesMap[$key]);
            } else {
                $this->assertEquals($expectedAttributeValue, $attributesMap[$key][0]);
            }
        }
    }

    public function testSetsSalesFrequencyAsZeroIfSortBySalesIsNotConfigured()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_attribute_values.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $this->storeConfigurationMock->expects($this->once())->method('getItemSortByMonthlySales')->willReturn(0);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals(['' => 0], $item->getSalesFrequency()->getValues());
    }

    public function testSetSalesFrequencyByPositionIfSortBySalesIsConfigured()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_different_positions.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $this->storeConfigurationMock->expects($this->once())->method('getItemSortByMonthlySales')->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals(['' => 5], $item->getSalesFrequency()->getValues());
    }

    /**
     * For this test the first item in the response is a non-main variation with the higher position
     * The second variation is main with lower position.
     * This test makes sure that the highest position is used and not from simply from the last or main variation
     */
    public function testUsesHighestPositionForSalesFrequency()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_different_positions.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = array_slice($variations->all(), 0, 2);

        $this->storeConfigurationMock->expects($this->once())->method('getItemSortByMonthlySales')->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals(['' => 1], $item->getSalesFrequency()->getValues());
    }

    public function testDimensionsWithoutValueAreIgnored(): void
    {
        $expectedExportedAttributes = [
            new Attribute('cat', ['Sessel & Hocker']),
            new Attribute('cat_url', ['/wohnzimmer/sessel-hocker/']),
            new Attribute('dimensions_height_mm', ['300', '400']),
            new Attribute('dimensions_length_mm', ['200', '300']),
            new Attribute('dimensions_width_mm', ['100', '200']),
            new Attribute('dimensions_weight_g', ['2000', '4000']),
            new Attribute('dimensions_weight_net_g', ['1000', '2000']),
        ];

        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variants_with_dimensions.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = array_slice($variations->all(), 0, 2);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $reflector = new ReflectionClass($item);
        $attributes = $reflector->getProperty('attributes');
        $attributeValues = $attributes->getValue($item);

        $this->assertEqualsCanonicalizing($expectedExportedAttributes, $attributeValues);
    }

    public function testCallistoUrlFormatIsUsedWhenConfigured(): void
    {
        $expectedUrlPath = 'awesome-url-path/somewhere-in-the-store';

        $this->exporterMock = $this->getExporter();

        $rawVariation = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($rawVariation);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);

        $this->storeConfigurationMock->expects($this->exactly(2))
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->storeConfigurationMock->expects($this->once())->method('getDefaultLanguage')
            ->willReturn('de');

        $this->registryServiceMock->expects($this->once())->method('getAllWebStores')->willReturn($webStores);
        $plentyShop = new PlentyShop([PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => true]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $text = new Text([
            'lang' => 'de',
            'name1' => 'Pretty awesome name!',
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => 'Easy, transparent, sexy',
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => 'That is the best item, and I am a bit longer text.',
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $expectedUrlPath,
            'keywords' => 'get me out',
        ]);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);

        $this->variationEntityMocks[] = $variations->first();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertSame(
            'https://plenty-testshop.de/' . $expectedUrlPath . '/a-0',
            $item->getUrl()->getValues()['']
        );
    }

    public function itemShowPleaseSelectProvider(): array
    {
        $baseUrlPath = 'awesome-url-path/somewhere-in-the-store';
        return [
            'url with variation id when "item.show_please_select" disabled' => [
                'plentyShopConfig' => [
                    PlentyShop::KEY_ITEM_SHOW_PLEASE_SELECT => false,
                    PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
                ],
                'baseUrlPath' => $baseUrlPath,
                'expectedProductUrl' => $baseUrlPath . '_0_1004'
            ],
            'url without variation id when "item.show_please_select" enabled' => [
                'plentyShopConfig' => [
                    PlentyShop::KEY_ITEM_SHOW_PLEASE_SELECT => true,
                    PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false,
                ],
                'baseUrlPath' => $baseUrlPath,
                'expectedProductUrl' => $baseUrlPath . '_0'
            ]
        ];
    }

    /**
     * @dataProvider itemShowPleaseSelectProvider
     */
    public function testPlentyShopUrlIncludesVariationIdWhenConfigured(
        array $plentyShopConfig,
        string $baseUrlPath,
        string $expectedProductUrl
    ): void {
        $this->exporterMock = $this->getExporter();

        $rawVariation = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($rawVariation);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);

        $this->storeConfigurationMock->expects($this->exactly(2))
            ->method('getDisplayItemName')
            ->willReturn(1);

        $this->storeConfigurationMock->expects($this->once())->method('getDefaultLanguage')
            ->willReturn('de');

        $this->registryServiceMock->expects($this->once())->method('getAllWebStores')->willReturn($webStores);

        $plentyShop = new PlentyShop($plentyShopConfig);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $text = new Text([
            'lang' => 'de',
            'name1' => 'Pretty awesome name!',
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => 'Easy, transparent, sexy',
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => 'That is the best item, and I am a bit longer text.',
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $baseUrlPath,
            'keywords' => 'get me out',
        ]);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);

        $this->variationEntityMocks[] = $variations->first();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertSame(
            'https://plenty-testshop.de/' . $expectedProductUrl,
            $item->getUrl()->getValues()['']
        );
    }
}
