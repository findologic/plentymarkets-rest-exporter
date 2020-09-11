<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\AttributeParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertySelectionParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\SalesPriceParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Variation as VariationWrapper;
use GuzzleHttp\Psr7\Response;
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

        $salesPriceResponse = $this->getMockResponse('SalesPriceResponse/response.json');
        $salesPrices = SalesPriceParser::parse($salesPriceResponse);

        $storesResponse = $this->getMockResponse('WebStoreResponse/response.json');
        $stores = WebStoreParser::parse($storesResponse);

        $propertyGroupResponse = $this->getMockResponse('PropertyGroupResponse/response.json');
        $propertyGroups = PropertyGroupParser::parse($propertyGroupResponse);

        $propertyResponse = $this->getMockResponse('PropertyResponse/response.json');
        $properties = PropertyParser::parse($propertyResponse);

        $propertySelectionResponse = $this->getMockResponse('PropertySelectionResponse/response.json');
        $propertySelections = PropertySelectionParser::parse($propertySelectionResponse);

        $this->registryMock->method('get')->willReturnOnConsecutiveCalls(
            $propertyGroups,
            $propertySelections,
            $categories,
            $salesPrices,
            $attributes,
            $stores,
            $properties
        );

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

        $this->assertTrue($wrapper->isMain());
        $this->assertEquals(0, $wrapper->getPosition());
        $this->assertEquals(16, $wrapper->getVatId());
        $this->assertEquals('S-000813-C', $wrapper->getNumber());
        $this->assertEquals('modeeeel', $wrapper->getModel());
        $this->assertEquals(1001, $wrapper->getId());
        $this->assertEquals(103, $wrapper->getItemId());
        $this->assertEquals(['3213213213213'], $wrapper->getBarcodes());
        $this->assertEquals(499.0, $wrapper->getPrice());
        $this->assertEquals(560.0, $wrapper->getInsteadPrice());

        $attributes = $wrapper->getAttributes();
        $this->assertCount(11, $attributes);
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

        $groups = $wrapper->getGroups();
        $this->assertCount(1, $groups);
        $this->assertEquals('0_', $groups[0]->getValue());

        $tags = $wrapper->getTags();
        $this->assertCount(2, $tags);
        $this->assertEquals('aaaa', $tags[0]->getValue());
        $this->assertEquals('', $tags[0]->getUsergroup());
        $this->assertEquals('keyword', $tags[0]->getValueName());
        $this->assertEquals('Zeta Tage', $tags[1]->getValue());
        $this->assertEquals('', $tags[1]->getUsergroup());
        $this->assertEquals('keyword', $tags[1]->getValueName());
    }

    public function testCharacteristicsWhichAreNotSearchableAreNotExported()
    {
        $response = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $response = json_decode($response, true);
        $response['entries'][1]['variationProperties'][0]['property']['isSearchable'] = false;
        $response = json_encode($response);
        $response = new Response(200, [], $response);
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(10, $wrapper->getAttributes());
    }

    public function testCharacteristicsOfTypeEmptyAndWithoutGroupIdAreNotExported()
    {
        $response = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $response = json_decode($response, true);
        $response['entries'][1]['variationProperties'][0]['property']['propertyGroupId'] = null;
        $response['entries'][1]['variationProperties'][0]['property']['valueType'] = 'empty';
        $response = json_encode($response);
        $response = new Response(200, [], $response);
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(10, $wrapper->getAttributes());
    }

    public function testCharacteristicsOfTypeEmptyAndWithNonExistantGroupIdAreNotExported()
    {
        $response = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $response = json_decode($response, true);
        $response['entries'][1]['variationProperties'][0]['property']['propertyGroupId'] = 12321367891;
        $response['entries'][1]['variationProperties'][0]['property']['valueType'] = 'empty';
        $response = json_encode($response);
        $response = new Response(200, [], $response);
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(10, $wrapper->getAttributes());
    }

    public function testPropertiesWithNoNameAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyId'] = 1232136789;
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testPropertiesWithNoValueAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyRelation']['cast'] = 'shortText';
        $rawResponse['entries'][1]['properties'][0]['relationValues'] = [];
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testItExportsTheFirstValueOfPropertyIfPropertyTypeIsInvalid()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyRelation']['cast'] = 'someRandomWrongType';
        $rawResponse['entries'][1]['properties'][0]['relationValues'][] = [
            'id' => 100,
            'value' => 1000,
            'propertyRelationId' => 10000,
            'lang' => 'en',
            'description' => 'description',
            'createdAt' => '2020-02-02',
            'updatedAt' => '2020-02-02'
        ];
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(10, $wrapper->getAttributes());
        $this->assertEquals(['1000'], $wrapper->getAttributes()[8]->getValues());
    }

    public function testPropertiesNotOfTypeItemAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['relationTypeIdentifier'] = 'somethingOtherThanItem';
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testPropertiesOfTypeSelectionWithNonexistantPropertyIdsAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyRelation']['cast'] = 'selection';
        $rawResponse['entries'][1]['properties'][0]['propertyId'] = 561894132654;
        $rawResponse['entries'][1]['properties'][0]['relationValues'][] = [
            'id' => 100,
            'value' => 1000,
            'propertyRelationId' => 10000,
            'lang' => 'en',
            'description' => 'description',
            'createdAt' => '2020-02-02',
            'updatedAt' => '2020-02-02'
        ];
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testPropertiesOfTypeSelectionWithNonexistantSelectedValueAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyRelation']['cast'] = 'selection';
        $rawResponse['entries'][1]['properties'][0]['relationValues'][] = [
            'id' => 100,
            'value' => 1000,
            'propertyRelationId' => 10000,
            'lang' => 'en',
            'description' => 'description',
            'createdAt' => '2020-02-02',
            'updatedAt' => '2020-02-02'
        ];
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testPropertiesThatDontHaveANameInTheSelectedLanguageAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyId'] = 8;
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(9, $wrapper->getAttributes());
    }

    public function testPropertiesOfCastEmptyWithInvalidPropertyIdOrNoGroupNameInSelectedLanguageAreNotExported()
    {
        $rawResponse = file_get_contents(__DIR__ . '/../MockData/ItemVariationResponse/response.json');
        $rawResponse = json_decode($rawResponse, true);
        $rawResponse['entries'][1]['properties'][0]['propertyId'] = 10;
        $rawResponse['entries'][1]['properties'][0]['propertyRelation']['cast'] = 'empty';
        $rawResponse['entries'][1]['properties'][1]['propertyId'] = 89762168461;
        $response = new Response(200, [], json_encode($rawResponse));
        $variationEntities = ItemVariationParser::parse($response);
        $variationEntity = $variationEntities->findOne(['id' => 1001]);

        $wrapper = new VariationWrapper(
            $this->defaultConfig,
            $this->registryMock,
            $variationEntity
        );

        $wrapper->processData();

        $this->assertCount(8, $wrapper->getAttributes());
    }
}
