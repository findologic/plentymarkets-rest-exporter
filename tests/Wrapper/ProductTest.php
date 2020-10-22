<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use Carbon\Carbon;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Base;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Product;
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

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->once())->method('getStandardVat')->willReturn($standardVat);

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

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $standardVat = VatParser::parseSingleEntityResponse($standardVatResponse);
        $this->registryServiceMock->expects($this->once())->method('getStandardVat')->willReturn($standardVat);

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
