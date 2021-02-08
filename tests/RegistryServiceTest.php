<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemPropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ManufacturerParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\VatParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegistryServiceTest extends TestCase
{
    use ResponseHelper;
    use ConfigHelper;

    /** @var RegistryService */
    private $registryService;

    /** @var Config */
    private $defaultConfig;

    /** @var Logger|MockObject */
    private $loggerMock;

    /** @var Client|MockObject */
    private $clientMock;

    /** @var Registry|MockObject */
    private $registryMock;

    public function setUp(): void
    {
        $this->defaultConfig = $this->getDefaultConfig();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryService = new RegistryService(
            $this->loggerMock,
            $this->loggerMock,
            $this->defaultConfig,
            $this->clientMock,
            $this->registryMock
        );
    }

    public function testRegistryIsWarmedUp(): void
    {
        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);
        $webStoreResponseBody = [
            $expectedWebStore->getData(),
            [
                'id' => 1,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));

        $parsedWebStoreResponse = WebStoreParser::parse($webStoreResponse);

        $categoryResponseBody = json_decode(
            $this->getMockResponse('CategoryResponse/one.json')->getBody()->__toString(),
            true
        );
        $parsedCategoryResponse = CategoryParser::parse($this->getMockResponse('CategoryResponse/one.json'));

        $expectedCategories = new CategoryResponse(
            1,
            0,
            true,
            [] // All categories are filtered out by the criteria.
        );
        $categoryResponse = new Response(200, [], json_encode($categoryResponseBody));

        $vatResponse = $this->getMockResponse('VatResponse/one.json');
        $expectedVat = VatParser::parse($vatResponse);

        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $expectedStandardVat = VatParser::parseSingleEntityResponse($standardVatResponse);

        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/rrp_normal_and_default.json');
        $expectedSalesPrice = SalesPriceParser::parse($salesPriceResponse);

        $attributeResponse = $this->getMockResponse('AttributeResponse/one.json');
        $expectedAttribute = AttributeParser::parse($attributeResponse);

        $manufacturerResponse = $this->getMockResponse('ManufacturerResponse/one.json');
        $expectedManufacturer = ManufacturerParser::parse($manufacturerResponse);

        $propertyResponse = $this->getMockResponse('PropertyResponse/one.json');
        $expectedProperties = PropertyParser::parse($propertyResponse);

        $itemPropertyResponse = $this->getMockResponse('ItemPropertyResponse/one.json');
        $expectedItemProperties = ItemPropertyParser::parse($itemPropertyResponse);

        $unitResponse = $this->getMockResponse('UnitResponse/one.json');
        $expectedUnits = UnitParser::parse($unitResponse);

        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $expectedPropertySelections = PropertySelectionParser::parse($propertySelectionResponse);

        $propertyGroupResponse = $this->getMockResponse('PropertyGroupResponse/one.json');
        $expectedGroups = PropertyGroupParser::parse($propertyGroupResponse);

        $pluginSetPluginsResponse = $this->getMockResponse('PluginFromSetResponse/one.json');

        $pluginConfigurationResponse = $this->getMockResponse('PluginConfigurationResponse/sample.json');
        $expectedPluginConfigurations = [
            'Findologic' => [
                'config.config2' => 'non default value',
                'config.config1' => 'default value'
            ]
        ];

        $this->clientMock->expects($this->exactly(14))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $webStoreResponse,
                $categoryResponse,
                $vatResponse,
                $standardVatResponse,
                $salesPriceResponse,
                $attributeResponse,
                $manufacturerResponse,
                $propertyResponse,
                $itemPropertyResponse,
                $unitResponse,
                $propertySelectionResponse,
                $propertyGroupResponse,
                $pluginSetPluginsResponse,
                $pluginConfigurationResponse
            );

        $registryKey = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->exactly(18))
            ->method('set')
            ->withConsecutive(
                [$registryKey . '_allWebStores', $parsedWebStoreResponse],
                [$registryKey . '_webStore', $expectedWebStore],
                [$registryKey . '_category_17', $parsedCategoryResponse->first()],
                [$registryKey . '_vat_1', $expectedVat->first()],
                [$registryKey . '_standardVat', $expectedStandardVat],
                [$registryKey . '_defaultPrice', $expectedSalesPrice->findOne([
                    'type' => 'default'
                ])],
                [$registryKey . '_salesPrice_1', $expectedSalesPrice->findOne([
                    'type' => 'default'
                ])],
                [$registryKey . '_defaultRrpPrice', $expectedSalesPrice->findOne([
                    'type' => 'rrp'
                ])],
                [$registryKey . '_salesPrice_2', $expectedSalesPrice->findOne([
                    'type' => 'rrp'
                ])],
                [$registryKey . '_salesPrice_3', $expectedSalesPrice->findOne([
                    'type' => 'somethingElse'
                ])],
                [$registryKey . '_attribute_1', $expectedAttribute->first()],
                [$registryKey . '_manufacturer_1', $expectedManufacturer->first()],
                [$registryKey . '_property_1', $expectedProperties->first()],
                [$registryKey . '_itemProperty_1', $expectedItemProperties->first()],
                [$registryKey . '_unit_1', $expectedUnits->first()],
                [$registryKey . '_propertySelections', $expectedPropertySelections],
                [$registryKey . '_propertyGroup_1', $expectedGroups->first()],
                [$registryKey . '_pluginConfigurations', $expectedPluginConfigurations],
                [$registryKey . '_categories', $expectedPluginConfigurations],
            );

        $this->registryMock->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $expectedWebStore,
                $expectedWebStore,
                $expectedWebStore,
                new CategoryResponse(1, 1, true, []),
                $expectedSalesPrice,
                $expectedAttribute,
                $expectedItemProperties,
                $expectedProperties,
                $expectedUnits
            );

        $this->registryService->warmUp();
    }

    public function testItFailsIfWebStoreDoesNotExist(): void
    {
        $expectedMultiShopId = 1337;

        $this->expectException(CustomerException::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find a web store with the multishop id "%d"',
            $expectedMultiShopId
        ));

        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'German Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);
        $webStoreResponseBody = [
            $expectedWebStore->getData(),
            [
                'id' => 1,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));

        $this->clientMock->expects($this->once())
            ->method('send')
            ->willReturnOnConsecutiveCalls($webStoreResponse);

        $this->defaultConfig->setMultiShopId($expectedMultiShopId);

        $this->registryService->warmUp();
    }

    public function testMissingPluginConfigurationPermissionsAreLoggedAndAllowTheExportToContinue(): void
    {
        $webStoreResponseBody = [
            [
                'id' => 0,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));
        $categoryResponseBody = json_decode(
            $this->getMockResponse('CategoryResponse/one.json')->getBody()->__toString(),
            true
        );
        $categoryResponse = new Response(200, [], json_encode($categoryResponseBody));
        $vatResponse = $this->getMockResponse('VatResponse/one.json');
        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/rrp_normal_and_default.json');
        $attributeResponse = $this->getMockResponse('AttributeResponse/one.json');
        $manufacturerResponse = $this->getMockResponse('ManufacturerResponse/one.json');
        $propertyResponse = $this->getMockResponse('PropertyResponse/one.json');
        $itemPropertyResponse = $this->getMockResponse('ItemPropertyResponse/one.json');
        $unitResponse = $this->getMockResponse('UnitResponse/one.json');
        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $propertyGroupResponse = $this->getMockResponse('PropertyGroupResponse/one.json');
        $pluginSetPluginsResponse = $this->getMockResponse('PluginFromSetResponse/one.json');

        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);

        $this->registryMock->method('get')->willReturnOnConsecutiveCalls(
            $expectedWebStore,
            $expectedWebStore,
            $expectedWebStore
        );

        $this->clientMock->method('send')->will(
            $this->onConsecutiveCalls(
                $webStoreResponse,
                $categoryResponse,
                $vatResponse,
                $standardVatResponse,
                $salesPriceResponse,
                $attributeResponse,
                $manufacturerResponse,
                $propertyResponse,
                $itemPropertyResponse,
                $unitResponse,
                $propertySelectionResponse,
                $propertyGroupResponse,
                $pluginSetPluginsResponse,
                $this->throwException(new \Exception('The REST client does not have access rights for method asdasd')),
            )
        );

        $this->loggerMock->expects($this->once())->method('error')->with(
            'Required permissions \'Plugins > Configurations > Show\' have not been granted. ' .
            'Product-URLs will be exported in Callisto format!'
        );

        $registryKey = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->at(20))->method('set')->with($registryKey . '_pluginConfigurations', []);

        $this->registryService->warmUp();
    }

    public function testUnknownPluginConfigurationFetchErrorsAreNotHandled(): void
    {
        $webStoreResponseBody = [
            [
                'id' => 0,
                'type' => 'plentymarkets',
                'storeIdentifier' => 12345,
                'name' => 'German Test Store',
                'pluginSetId' => 46,
                'configuration' => []
            ]
        ];
        $webStoreResponse = new Response(200, [], json_encode($webStoreResponseBody));
        $categoryResponseBody = json_decode(
            $this->getMockResponse('CategoryResponse/one.json')->getBody()->__toString(),
            true
        );
        $categoryResponse = new Response(200, [], json_encode($categoryResponseBody));
        $vatResponse = $this->getMockResponse('VatResponse/one.json');
        $standardVatResponse = $this->getMockResponse('VatResponse/standard_vat.json');
        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/rrp_normal_and_default.json');
        $attributeResponse = $this->getMockResponse('AttributeResponse/one.json');
        $manufacturerResponse = $this->getMockResponse('ManufacturerResponse/one.json');
        $propertyResponse = $this->getMockResponse('PropertyResponse/one.json');
        $itemPropertyResponse = $this->getMockResponse('ItemPropertyResponse/one.json');
        $unitResponse = $this->getMockResponse('UnitResponse/one.json');
        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $propertyGroupResponse = $this->getMockResponse('PropertyGroupResponse/one.json');
        $pluginSetPluginsResponse = $this->getMockResponse('PluginFromSetResponse/one.json');

        $expectedWebStore = new WebStore([
            'id' => 0,
            'type' => 'plentymarkets',
            'storeIdentifier' => 12345,
            'name' => 'Test Store',
            'pluginSetId' => 44,
            'configuration' => []
        ]);

        $this->registryMock->method('get')->willReturnOnConsecutiveCalls(
            $expectedWebStore,
            $expectedWebStore,
            $expectedWebStore
        );

        $exception = new \Exception('Some unknown error message');

        $this->clientMock->method('send')->will(
            $this->onConsecutiveCalls(
                $webStoreResponse,
                $categoryResponse,
                $vatResponse,
                $standardVatResponse,
                $salesPriceResponse,
                $attributeResponse,
                $manufacturerResponse,
                $propertyResponse,
                $itemPropertyResponse,
                $unitResponse,
                $propertySelectionResponse,
                $propertyGroupResponse,
                $pluginSetPluginsResponse,
                $this->throwException($exception),
            )
        );

        $this->expectExceptionObject($exception);

        $this->registryService->warmUp();
    }

    public function testGetWebStoreIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $parsed = WebStoreParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$key . '_webStore'],
                [$key . '_allWebStores'],
            )
            ->willReturnOnConsecutiveCalls(
                $parsed->first(),
                $parsed
            );

        $this->assertEquals($parsed->first(), $this->registryService->getWebStore());
        $this->assertEquals($parsed, $this->registryService->getAllWebStores());
    }

    public function testGetCategoryIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('CategoryResponse/one.json');
        $parsed = CategoryParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_category_17')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getCategory(17));
    }

    public function testGetAttributeIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('AttributeResponse/one.json');
        $parsed = AttributeParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_attribute_1')
            ->willReturnOnConsecutiveCalls($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getAttribute(1));
    }

    public function testGetVatIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('VatResponse/one.json');
        $parsed = VatParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_vat_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getVat(1));
    }

    public function testGetSalesPriceIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('SalesPriceResponse/one.json');
        $parsed = SalesPriceParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_salesPrice_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getSalesPrice(1));
    }

    public function testGetManufacturerIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('ManufacturerResponse/one.json');
        $parsed = ManufacturerParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_manufacturer_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getManufacturer(1));
    }

    public function testGetPropertyIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('PropertyResponse/one.json');
        $parsed = PropertyParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_property_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getProperty(1));
    }

    public function testGetItemPropertyIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('ItemPropertyResponse/one.json');
        $parsed = ItemPropertyParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_itemProperty_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getItemProperty(1));
    }

    public function testGetUnitIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('UnitResponse/one.json');
        $parsed = UnitParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_unit_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getUnit(1));
    }

    public function testGetPropertySelectionsIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $parsed = PropertySelectionParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_propertySelections')
            ->willReturn($parsed);

        $this->assertEquals($parsed, $this->registryService->getPropertySelections());
    }

    public function testGetPropertyGroupIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('PropertyGroupResponse/one.json');
        $parsed = PropertyGroupParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_propertyGroup_1')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first(), $this->registryService->getPropertyGroup(1));
    }

    public function testGetPriceIdIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('SalesPriceResponse/one.json');
        $parsed = SalesPriceParser::parse($rawResponse);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_defaultPrice')
            ->willReturn($parsed->first());

        $this->assertEquals($parsed->first()->getId(), $this->registryService->getPriceId());
    }

    public function testGetRrpIdIsProperlyFetchedFromRegistry(): void
    {
        $rawResponse = $this->getMockResponse('SalesPriceResponse/response.json');
        $parsed = SalesPriceParser::parse($rawResponse);
        $expected = $parsed->findOne([
            'type' => 'rrp'
        ]);

        $key = md5($this->defaultConfig->getDomain());

        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_defaultRrpId')
            ->willReturn($expected);

        $this->assertEquals($expected->getId(), $this->registryService->getRrpId());
    }

    public function testGetRrpIdIsFetchedFromConfigIfSet(): void
    {
        $rawResponse = $this->getMockResponse('SalesPriceResponse/response.json');
        $parsed = SalesPriceParser::parse($rawResponse);
        $default = $parsed->findOne([
            'type' => 'rrp'
        ]);

        $expectedRrpId = 69;

        $this->defaultConfig->setRrpId($expectedRrpId);

        $key = md5($this->defaultConfig->getDomain());
        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_defaultRrpId')
            ->willReturn($default);

        $this->assertEquals($expectedRrpId, $this->registryService->getRrpId());
    }

    public function testGetStandardVat(): void
    {
        $rawResponse = $this->getMockResponse('VatResponse/response.json');
        $parsed = VatParser::parse($rawResponse);
        $standard = $parsed->first();

        $key = md5($this->defaultConfig->getDomain());
        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_standardVat')
            ->willReturn($standard);

        $this->assertEquals($standard, $this->registryService->getStandardVat());
    }

    public function testGetPluginConfigurations(): void
    {
        $configData = ['plugin' => ['config.key' => 'config.value']];
        $key = md5($this->defaultConfig->getDomain());
        $this->registryMock->expects($this->once())
            ->method('get')
            ->with($key . '_pluginConfigurations')
            ->willReturn($configData);

        $this->assertEquals($configData, $this->registryService->getPluginConfigurations());
    }
}
