<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use Carbon\Carbon;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Base;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Product;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    use ConfigHelper;
    use ResponseHelper;

    /** @var Exporter|MockObject */
    private $exporterMock;

    /** @var Config */
    private $config;

    /** @var Item|MockObject */
    private $itemMock;

    /** @var Configuration|MockObject */
    private $storeConfigurationMock;

    /** @var RegistryService|MockObject */
    private $registryServiceMock;

    /** @var Variation[]|MockObject[] */
    private $variationEntityMocks = [];

    protected function setUp(): void
    {
        $this->exporterMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getDefaultConfig();
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeConfigurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryServiceMock = $this->getMockBuilder(RegistryService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->any())->method('getStandardVat')->willReturn($standardVat);
    }

    public function testProductWithoutVariationsIsNotExported(): void
    {
        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame('Product has no variations.', $product->getReason());
    }

    public function testProductWithOnlyInactiveVariationsIsNotExported(): void
    {
        $variationMock = $this->getMockBuilder(Variation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $baseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $variationMock->expects($this->once())->method('getBase')->willReturn($baseMock);
        $baseMock->expects($this->once())->method('isActive')->willReturn(false);

        $this->variationEntityMocks[] = $variationMock;

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame(
            'All assigned variations are not exportable (inactive, no longer available, etc.)',
            $product->getReason()
        );
    }

    public function testProductWithInvisibleVariationsIsNotExported(): void
    {
        $variationMock = $this->getMockBuilder(Variation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $baseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $variationMock->expects($this->exactly(2))->method('getBase')->willReturn($baseMock);
        $baseMock->expects($this->once())->method('isActive')->willReturn(true);
        $baseMock->expects($this->once())->method('getAutomaticListVisibility')
            ->willReturn(0);

        $this->variationEntityMocks[] = $variationMock;

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame(
            'All assigned variations are not exportable (inactive, no longer available, etc.)',
            $product->getReason()
        );
    }

    public function testProductWithNoLongerAvailableVariationsIsNotExported(): void
    {
        $variationMock = $this->getMockBuilder(Variation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $baseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $variationMock->expects($this->exactly(3))->method('getBase')->willReturn($baseMock);
        $baseMock->expects($this->once())->method('isActive')
            ->willReturn(true);
        $baseMock->expects($this->once())->method('getAutomaticListVisibility')
            ->willReturn(3);
        $baseMock->expects($this->once())->method('getAvailableUntil')
            ->willReturn(Carbon::now()->subSeconds(3));

        $this->variationEntityMocks[] = $variationMock;

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame(
            'All assigned variations are not exportable (inactive, no longer available, etc.)',
            $product->getReason()
        );
    }

    public function testProductWithVariationsConfigAvailabilityIdIsNotExported(): void
    {
        $configAvailabilityId = 7;
        $this->config->setAvailabilityId($configAvailabilityId);

        $variationMock = $this->getMockBuilder(Variation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $baseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $variationMock->expects($this->exactly(4))->method('getBase')->willReturn($baseMock);
        $baseMock->expects($this->once())->method('isActive')
            ->willReturn(true);
        $baseMock->expects($this->once())->method('getAutomaticListVisibility')
            ->willReturn(3);
        $baseMock->expects($this->once())->method('getAvailableUntil')
            ->willReturn(Carbon::now()->addSeconds(5));
        $baseMock->expects($this->once())->method('getAvailability')
            ->willReturn($configAvailabilityId);

        $this->variationEntityMocks[] = $variationMock;

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame(
            'All assigned variations are not exportable (inactive, no longer available, etc.)',
            $product->getReason()
        );
    }

    public function testProductIsSuccessfullyWrapped(): void
    {
        $expectedName = 'Pretty awesome name!';
        $expectedSummary = 'Easy, transparent, sexy';
        $expectedDescription = 'That is the best item, and I am a bit longer text.';
        $expectedUrlPath = 'awesome-url-path/somewhere-in-the-store';

        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $rawVariation = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($rawVariation);

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);

        $rawCategories = $this->getMockResponse('CategoryResponse/one.json');
        $categories = CategoryParser::parse($rawCategories);

        $rawManufacturers = $this->getMockResponse('ManufacturerResponse/one.json');
        $manufacturers = ManufacturerParser::parse($rawManufacturers);

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

        $text = new Text([
            'lang' => 'de',
            'name1' => $expectedName,
            'name2' => 'wrong',
            'name3' => 'wrong',
            'shortDescription' => $expectedSummary,
            'metaDescription' => 'my father gave me a small loan of a million dollar.',
            'description' => $expectedDescription,
            'technicalData' => 'Interesting technical information.',
            'urlPath' => $expectedUrlPath,
            'keywords' => 'get me out',
        ]);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);

        $this->itemMock->expects($this->once())
            ->method('getManufacturerId')
            ->willReturn(1);

        $this->variationEntityMocks[] = $variations->findOne(['id' => 1004]);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertSame($expectedName, $item->getName()->getValues()['']);
        $this->assertSame($expectedSummary, $item->getSummary()->getValues()['']);
        $this->assertSame($expectedDescription, $item->getDescription()->getValues()['']);
        $this->assertSame(
            'https://plenty-testshop.de/' . $expectedUrlPath . '/a-0',
            $item->getUrl()->getValues()['']
        );
        $this->assertTrue(
            \DateTime::createFromFormat(\DateTime::ISO8601, $item->getDateAdded()->getValues()['']) !== false
        );
    }

    public function testLanguagePrefixIsAddedToUrlInCaseLanguageIsAvailableButNotDefaultLanguage(): void
    {
        $expectedUrlPath = 'awesome-url-path/somewhere-in-the-store';
        $expectedLanguagePrefix = 'de';

        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

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
            'https://plenty-testshop.de/' . $expectedLanguagePrefix . '/' . $expectedUrlPath . '/a-0',
            $item->getUrl()->getValues()['']
        );
    }

    public function testSortIsSetByTheMainVariation(): void
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 3);
        $entries[0]['base']['isMain'] = false;
        $entries[0]['base']['position'] = 1;
        $entries[1]['base']['isMain'] = true;
        $entries[1]['base']['position'] = 2;
        $entries[2]['base']['isMain'] = false;
        $entries[2]['base']['position'] = 3;
        $variationResponse['entries'] = $entries;
        $variationResponse =  $this->createResponseFromArray($variationResponse);

        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals($item->getSort()->getValues(), ['' => 2]);
    }

    public function testKeywordsAreSetFromAllVariations(): void
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 2);
        $entries[0]['tags'] = [
            [
                'tagId' => 1,
                'tag' => [
                    'tagName' => 'tag1',
                    'createdAt' => '2019-03-19 14:07:52',
                    'updatedAt' => '2020-03-03 15:30:19',
                    'names' => [
                        [
                            'id' => 1,
                            'tagId' => 1,
                            'tagLang' => 'de',
                            'tagName' => 'de tag 1'
                        ],
                        [
                            'id' => 2,
                            'tagId' => 1,
                            'tagLang' => 'en',
                            'tagName' => 'en tag 1'
                        ]
                    ]
                ]
            ],
            [
                'tagId' => 2,
                'tag' => [
                    'tagName' => 'tag2',
                    'createdAt' => '2019-03-19 14:07:52',
                    'updatedAt' => '2020-03-03 15:30:19',
                    'names' => [
                        [
                            'id' => 3,
                            'tagId' => 2,
                            'tagLang' => 'de',
                            'tagName' => 'de tag 2'
                        ],
                    ]
                ]
            ]
        ];
        $entries[1]['tags'] = [
            [
                'tagId' => 3,
                'tag' => [
                    'tagName' => 'tag3',
                    'createdAt' => '2019-03-19 14:07:52',
                    'updatedAt' => '2020-03-03 15:30:19',
                    'names' => [
                        [
                            'id' => 5,
                            'tagId' => 3,
                            'tagLang' => 'de',
                            'tagName' => 'de tag 3'
                        ],
                    ]
                ]
            ]
        ];
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

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

        // TODO: check item's keyword property directly once keywords getter is implemented
        $line = $item->getCsvFragment();
        $columnValues = explode("\t", $line);
        $this->assertEquals('de tag 1,de tag 2,de tag 3,keywords from product', $columnValues[12]);
    }

    public function testPriceAndInsteadPriceIsSetByLowestValues(): void
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 3);
        $entries[0]['salesPrices'][0]['salesPriceId'] = 0;
        $entries[0]['salesPrices'][0]['price'] = 100;
        $entries[0]['salesPrices'][1]['salesPriceId'] = 1;
        $entries[0]['salesPrices'][1]['price'] = 150;
        $entries[1]['salesPrices'][0]['salesPriceId'] = 0;
        $entries[1]['salesPrices'][0]['price'] = 50;
        $entries[1]['salesPrices'][1]['salesPriceId'] = 1;
        $entries[1]['salesPrices'][1]['price'] = 100;
        $entries[2]['salesPrices'][0]['salesPriceId'] = 0;
        $entries[2]['salesPrices'][0]['price'] = 250;
        $entries[2]['salesPrices'][1]['salesPriceId'] = 1;
        $entries[2]['salesPrices'][1]['price'] = 300;
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $this->registryServiceMock->expects($this->any())->method('getRrpId')->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEquals($item->getPrice()->getValues(), ['' => 50]);
        $this->assertEquals($item->getInsteadPrice(), 100);
    }

    public function testImageOfFirstVariationIsUsed(): void
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 3);
        $image = $entries[0]['images'][0];
        $image['availabilities'] = [['type' => Availability::STORE, 'value' => 139, 'imageId' => 46]];
        $entries[0]['images'] = [];
        $entries[0]['base']['images'] = [];
        $image['urlMiddle'] = 'FirstAvailableImage.jpg';
        $entries[1]['images'] = [$image];
        $entries[1]['base']['images'] = [];
        $image['urlMiddle'] = 'FirstAvailableImage.jpg';
        $entries[2]['images'] = [$image];
        $entries[2]['base']['images'] = [];
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $product = $this->getProduct();
        $item = $product->processProductData();

        // TODO: check item's images property directly once images getter is implemented
        $line = $item->getCsvFragment();
        $columnValues = explode("\t", $line);
        $this->assertEquals('FirstAvailableImage.jpg', $columnValues[10]);
    }

    public function testGroupsAreSetFromAllVariations()
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 3);
        $entries[0]['clients'] = [['plentyId' => 1234]];
        $entries[1]['clients'] = [['plentyId' => 2345]];
        $entries[2]['clients'] = [['plentyId' => 3456]];
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        // TODO: check item's groups property directly once groups getter is implemented
        $line = $item->getCsvFragment();
        $columnValues = explode("\t", $line);
        $this->assertEquals('0_,1_', $columnValues[13]);
    }

    public function testOrderNumbersAreSetFromAllVariations()
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 2);
        $entries[0]['base']['number'] = 1;
        $entries[0]['base']['model'] = 11;
        $entries[0]['base']['itemId'] = 111;
        $entries[0]['id'] = 1111;
        $entries[0]['barcodes'] = [['barcodeId' => 1, 'code' => 11111], ['barcodeId' => 1, 'code' => 111111]];
        $entries[1]['base']['number'] = 2;
        $entries[1]['base']['model'] = 22;
        $entries[1]['base']['itemId'] = 222;
        $entries[1]['id'] = 2222;
        $entries[1]['barcodes'] = [['barcodeId' => 3, 'code' => 22222], ['barcodeId' => 4, 'code' => 222222]];
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        // TODO: check item's order numbers property directly once order numbers getter is implemented
        $line = $item->getCsvFragment();
        $columnValues = explode("\t", $line);
        $this->assertEquals('1|11|1111|111|11111|111111|2|22|2222|222|22222|222222', $columnValues[1]);
    }

    public function testAttributesAreSetFromAllVariations()
    {
        $this->exporterMock = Exporter::create(Exporter::TYPE_CSV);

        $variationResponse = $this->getResponseAsArray('Pim/Variations/response.json');
        $entries = array_slice($variationResponse['entries'], 0, 3);
        $attribute = $entries[0]['attributeValues'][0];
        $attribute['attributeId'] = 2;
        $attribute['attributeValue']['backendName'] = 'valueeeee';
        $entries[1]['attributeValues'] = [$attribute];
        $attribute['attributeId'] = 1;
        $attribute['attributeValue']['backendName'] = '2121asdsdf';
        $entries[2]['attributeValues'] = [$attribute];
        $variationResponse['entries'] = $entries;
        $variationResponse = $this->createResponseFromArray($variationResponse);

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
        $line = $item->getCsvFragment();
        $columnValues = explode("\t", $line);
        $this->assertEquals('Couch+color=purple&Couch+color=valueeeee&Test=2121asdsdf', $columnValues[11]);
    }

    private function getProduct(): Product
    {
        return new Product(
            $this->exporterMock,
            $this->config,
            $this->storeConfigurationMock,
            $this->registryServiceMock,
            $this->itemMock,
            $this->variationEntityMocks
        );
    }
}
