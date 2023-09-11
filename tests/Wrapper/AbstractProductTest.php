<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use DateTime;
use Carbon\Carbon;
use ReflectionClass;
use DateTimeInterface;
use FINDOLOGIC\Export\Exporter;
use PHPUnit\Framework\TestCase;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Enums\ExporterType;
use PHPUnit\Framework\MockObject\MockObject;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\PlentyShop;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Product;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ItemHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Base;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;

abstract class AbstractProductTest extends TestCase
{
    use ConfigHelper;
    use ResponseHelper;
    use ItemHelper;

    protected const DEFAULT_TEXTS = [
        'lang' => 'de',
        'name1' => 'name1',
        'name2' => '',
        'name3' => '',
        'shortDescription' => '',
        'metaDescription' => '',
        'description' => '',
        'technicalData' => '',
        'keywords' => '',
        'urlPath' => 'name-path',
    ];

    protected Exporter|MockObject $exporterMock;

    protected Config $config;

    protected Item|MockObject $itemMock;

    protected Configuration|MockObject $storeConfigurationMock;

    protected RegistryService|MockObject $registryServiceMock;

    /** @var Variation[]|MockObject[] */
    protected array $variationEntityMocks = [];

    protected function setUp(bool $useVariants = false): void
    {
        $this->exporterMock = $this->getMockBuilder(Exporter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getDefaultConfig();
        $this->config->setUseVariants($useVariants);
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

        $categoryResponse = $this->getMockResponse('CategoryResponse/one.json');
        $parsedCategoryResponse = CategoryParser::parse($categoryResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getCategory')
            ->willReturn($parsedCategoryResponse->first());

        $webstoreResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $parsedWebstoreResponse = WebStoreParser::parse($webstoreResponse);

        $this->registryServiceMock->expects($this->any())
            ->method('getWebstore')
            ->willReturn($parsedWebstoreResponse->first());
    }

    public function testProductWithoutVariationsIsNotExported(): void
    {
        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame('Product has no variations.', $product->getReason());
    }

    /**
     * @dataProvider correctManufacturerIsExportedTestProvider
     */
    public function testCorrectManufacturerNameIsExported(
        string $manufacturerMockResponse,
        string $expectedResult
    ): void {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawManufacturers = $this->getMockResponse($manufacturerMockResponse);
        $manufacturers = ManufacturerParser::parse($rawManufacturers);

        $this->registryServiceMock->expects($this->once())
            ->method('getManufacturer')
            ->willReturn($manufacturers->first());

        $this->itemMock->expects($this->once())
            ->method('getManufacturerId')
            ->willReturn(1);

        $product = $this->getProduct();
        $item = $product->processProductData();
        $attributesMap = $this->getMappedAttributes($item);

        $this->assertEquals($expectedResult, $attributesMap['vendor'][0]);
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
            'All assigned variations are not exportable (inactive, no longer available, no categories etc.)',
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
            'All assigned variations are not exportable (inactive, no longer available, no categories etc.)',
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
            'All assigned variations are not exportable (inactive, no longer available, no categories etc.)',
            $product->getReason()
        );
    }

    public function testProductWithAllVariationsMatchingConfigurationAvailabilityIdAreNotExported()
    {
        $this->exporterMock = $this->getExporter();

        $this->config->setAvailabilityId(5);

        $rawVariations = $this->getMockResponse('Pim/Variations/variations_with_5_for_availability_id.json');
        $variations = PimVariationsParser::parse($rawVariations);
        $this->variationEntityMocks = $variations->all();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertNull($item);
        $this->assertSame(
            'All assigned variations are not exportable (inactive, no longer available, no categories etc.)',
            $product->getReason()
        );
    }

    public function testProductWithoutImagesNotFail(): void
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_item_without_any_images_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $plentyShop = new PlentyShop([PlentyShop::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN => false]);
        $this->registryServiceMock->method('getPlentyShop')->willReturn($plentyShop);

        $product = $this->getProduct();
        $item = $product->processProductData();

        $this->assertEmpty($item->getImages());
    }

    public function testCheapestVariationIsUsed()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/response_for_lowest_price_test.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $product = $this->getProduct();
        $item = $product->processProductData();

        $itemProperties = $this->getArrayFirstElement($item->getProperties());
        $this->assertEquals(1005, $itemProperties['variation_id']);
    }

    public function testGroupsAreSetFromAllVariations()
    {
        $this->setDefaultText();
        $this->setDefaultDisplayName();
        $this->setDefaultLanguage();
        $expectedGroups = ['0_', '1_'];
        $this->exporterMock = $this->getExporter();

        $variationResponse = $this->getMockResponse('Pim/Variations/variations_with_different_clients.json');
        $variations = PimVariationsParser::parse($variationResponse);
        $this->variationEntityMocks = $variations->all();

        $rawWebStores = $this->getMockResponse('WebStoreResponse/response.json');
        $webStores = WebStoreParser::parse($rawWebStores);
        $this->registryServiceMock->expects($this->any())->method('getAllWebStores')->willReturn($webStores);

        $product = $this->getProduct();
        $item = $product->processProductData();

        foreach ($item->getVariants() as $key => $variant) {
            $variantGroups = $this->getItemGroups($variant);
            if (isset($expectedGroups[$key])) {
                $this->assertEquals($expectedGroups[$key], $variantGroups[0]);
            }
        }
    }

    protected function getProduct(): Product
    {
        return new Product(
            $this->exporterMock,
            $this->config,
            $this->storeConfigurationMock,
            $this->registryServiceMock,
            $this->getPropertySelections(),
            $this->itemMock,
            $this->variationEntityMocks,
            Product::WRAP_MODE_DEFAULT
        );
    }

    private function getPropertySelections(): PropertySelectionResponse
    {
        return PropertySelectionParser::parse(
            $this->getMockResponse('PropertySelectionResponse/response.json')
        );
    }

    protected function getItem(array $data): Item
    {
        return new Item($data);
    }

    protected function getExporter(): Exporter
    {
        return Exporter::create(ExporterType::XML, 100);
    }

    public function cheapestVariationIsExportedTestProvider(): array
    {
        return [
            'item without images (no default image), no image exported' => [
                'Pim/Variations/response_for_cheapest_without_images.json',
                '',
                '20.43',
                'https://plenty-testshop.de/urlPath_0_6557'
            ],
            'cheapest with zero price variation' => [
                'Pim/Variations/response_for_cheapest_price_test.json',
                'https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/119/middle/exportedImage.jpeg',
                '11.33',
                'https://plenty-testshop.de/urlPath_0_1181'
            ],
            'cheapest without store availability' => [
                'Pim/Variations/response_for_cheapest_price_test_with_no_store_availability_for_image.json',
                'https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/119/middle/exportedImage.jpeg',
                '0.01',
                'https://plenty-testshop.de/urlPath_0_1181'
            ],
            'cheapest without an image, default image is used' => [
                'Pim/Variations/response_for_cheapest_price_variation_without_image.json',
                'https://cdn03.plentymarkets.com/v3b53of2xcyu/item/images/119/middle/119-Relaxsessel-Woddenfir.jpg',
                '1.00',
                'https://plenty-testshop.de/urlPath_0_1179'
            ]
        ];
    }

    public function correctManufacturerIsExportedTestProvider(): array
    {
        return [
            'manufacturer has external name, external name is exported' => [
                'ManufacturerResponse/one.json',
                'externalNameA',
            ],
            'manufacturer has no external name, original name is exported' => [
                'ManufacturerResponse/without_external_name.json',
                'nameA',
            ],
        ];
    }

    public function attributesAreSetFromAllVariationsTestProvider(): array
    {
        return [
            'has the same exported attribute' => [
                [
                    'cat' => 'Sessel & Hocker',
                    'cat_url' => '/wohnzimmer/sessel-hocker/',
                    'couch color de' => ['lila', 'valueeeee'],
                    'test de' => 'some test attribute value in German'
                ]
            ],
        ];
    }

    public function exportFreeFieldsConfigurationTestProvider(): array
    {
        return [
            'using default values' => [
                [],
                ['cat' => 'Sessel & Hocker', 'cat_url' => '/wohnzimmer/sessel-hocker/', 'free1' => '0']
            ],
            'free fields enabled' => [
                [
                    'exportFreeTextFields' => true,
                ],
                ['cat' => 'Sessel & Hocker', 'cat_url' => '/wohnzimmer/sessel-hocker/', 'free1' => '0']
            ],
            'free fields disabled' => [
                [
                    'exportFreeTextFields' => false,
                ],
                ['cat' => 'Sessel & Hocker', 'cat_url' => '/wohnzimmer/sessel-hocker/']
            ]
        ];
    }

    public function orderNumberExportConfigurationTestProvider(): array
    {
        return [
            'using default values' => [
                [],
                ['1', '11', '1111', '111', '11111', '111111', '2', '22', '2222', '222', '22222', '222222']
            ],
            'all fields enabled' => [
                [
                    'exportOrdernumberProductId' => true,
                    'exportOrdernumberVariantId' => true,
                    'exportOrdernumberVariantNumber' => true,
                    'exportOrdernumberVariantModel' => true,
                    'exportOrdernumberVariantBarcodes' => true
                ],
                ['1', '11', '1111', '111', '11111', '111111', '2', '22', '2222', '222', '22222', '222222']
            ],
            'all fields disabled' => [
                [
                    'exportOrdernumberProductId' => false,
                    'exportOrdernumberVariantId' => false,
                    'exportOrdernumberVariantNumber' => false,
                    'exportOrdernumberVariantModel' => false,
                    'exportOrdernumberVariantBarcodes' => false
                ],
                []
            ],
            'product id disabled' => [
                [
                    'exportOrdernumberProductId' => false,
                ],
                ['1', '11', '1111', '11111', '111111', '2', '22', '2222', '22222', '222222']
            ],
            'variant id disabled' => [
                [
                    'exportOrdernumberVariantId' => false,
                ],
                ['1', '11', '111', '11111', '111111', '2', '22', '222', '22222', '222222']
            ],
            'variant number disabled' => [
                [
                    'exportOrdernumberVariantNumber' => false,
                ],
                ['11', '1111', '111', '11111', '111111', '22', '2222', '222', '22222', '222222']
            ],
            'variant model disabled' => [
                [
                    'exportOrdernumberVariantModel' => false,
                ],
                ['1', '1111', '111', '11111', '111111', '2', '2222', '222', '22222', '222222']
            ],
            'variant barcodes disabled' => [
                [
                    'exportOrdernumberVariantBarcodes' => false,
                ],
                ['1', '11', '1111', '111', '2', '22', '2222', '222']
            ],
            'various fields disabled' => [
                [
                    'exportOrdernumberProductId' => false,
                    'exportOrdernumberVariantNumber' => false,
                    'exportOrdernumberVariantBarcodes' => false
                ],
                ['11', '1111', '22', '2222']
            ]
        ];
    }

    protected function setDefaultText(): void
    {
        $text = new Text(self::DEFAULT_TEXTS);

        $this->itemMock->expects($this->once())
            ->method('getTexts')
            ->willReturn([$text]);
    }

    protected function setDefaultDisplayName(): void
    {
        $this->storeConfigurationMock->expects($this->any())
            ->method('getDisplayItemName')
            ->willReturn(1);
    }

    protected function setDefaultLanguage(): void
    {
        $this->storeConfigurationMock->expects($this->any())
            ->method('getDefaultLanguage')
            ->willReturn('de');
    }
}
