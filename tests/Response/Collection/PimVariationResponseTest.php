<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Parser\PimVariationsParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Barcode;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Base;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Image;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\SalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Unit;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PimVariationResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $pimVariationResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('Pim/Variations/response.json');
        $this->pimVariationResponse = PimVariationsParser::parse($this->response);
    }

    public function testPimVariationDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $variations = $this->pimVariationResponse->all();

        /** @var Variation $variation */
        $variation = $this->pimVariationResponse->first();
        $rawVariation = $responseData['entries'][0];
        $data = $variation->getData();
        $this->assertContainsOnlyInstancesOf(Category::class, $data['categories']);
        $this->assertContainsOnlyInstancesOf(Barcode::class, $data['barcodes']);
        $this->assertContainsOnlyInstancesOf(Attribute::class, $data['attributeValues']);
        $this->assertContainsOnlyInstancesOf(Client::class, $data['clients']);
        $this->assertContainsOnlyInstancesOf(SalesPrice::class, $data['salesPrices']);
        $this->assertContainsOnlyInstancesOf(Property::class, $data['properties']);
        $this->assertContainsOnlyInstancesOf(Tag::class, $data['tags']);
        $this->assertContainsOnlyInstancesOf(Image::class, $data['images']);
        $this->assertInstanceOf(Base::class, $data['base']);

        $category = $variation->getCategories()[0];
        $this->assertSame(20, $category->getId());
        $this->assertSame(0, $category->getPosition());
        $this->assertTrue($category->isNeckermannPrimary());
        $this->assertEquals($rawVariation['categories'][0], $category->getData());

        $barcode = $variation->getBarcodes()[0];
        $this->assertSame(1, $barcode->getId());
        $this->assertSame('3213213213213', $barcode->getCode());
        $this->assertEquals($rawVariation['barcodes'][0], $barcode->getData());

        $attribute = $variation->getAttributeValues()[0];
        $this->assertSame(1, $attribute->getId());
        $this->assertSame(1, $attribute->getValueSetId());
        $this->assertSame(1, $attribute->getValueId());
        $rawAttributeValue = $rawVariation['attributeValues'][0]['attributeValue'];
        unset($rawVariation['attributeValues'][0]['attributeValue']);
        $expectedAttributeData = $attribute->getData();
        unset($expectedAttributeData['attributeValue']);
        $this->assertEquals($expectedAttributeData, $rawVariation['attributeValues'][0]);

        $attributeValue = $attribute->getValue();
        $this->assertSame(1, $attributeValue->getId());
        $this->assertSame(1, $attributeValue->getAttributeId());
        $this->assertSame(1, $attributeValue->getPosition());
        $this->assertSame('', $attributeValue->getImage());
        $this->assertSame('', $attributeValue->getComment());
        $this->assertSame('purple', $attributeValue->getBackendName());
        $this->assertSame('', $attributeValue->getAmazonValue());
        $this->assertSame('', $attributeValue->getOttoValue());
        $this->assertSame('', $attributeValue->getNeckermannAtEpValue());
        $this->assertSame('', $attributeValue->getLaRedouteValue());
        $this->assertSame('', $attributeValue->getTracdelightValue());
        $this->assertSame(0.0, $attributeValue->getPercentageDistribution());
        $this->assertInstanceOf(DateTimeInterface::class, $attributeValue->getUpdatedAt());
        $rawName = $rawAttributeValue['valueNames'][0];
        unset($rawAttributeValue['valueNames']);
        $expectedAttributeValueData = $attributeValue->getData();
        unset($expectedAttributeValueData['valueNames']);
        $this->assertEquals($expectedAttributeValueData, $rawAttributeValue);

        $attributeValueName = $attributeValue->getNames()[0];
        $this->assertSame(1, $attributeValueName->getId());
        $this->assertSame('lila', $attributeValueName->getName());
        $this->assertSame('de', $attributeValueName->getLang());
        $this->assertEquals($rawName, $attributeValueName->getData());

        $client = $variation->getClients()[0];
        $this->assertSame(1234, $client->getPlentyId());
        $this->assertEquals($rawVariation['clients'][0], $client->getData());

        $salesPrice = $variation->getSalesPrices()[0];
        $this->assertSame(0, $salesPrice->getId());
        $this->assertSame(279.0, $salesPrice->getPrice());
        $this->assertEquals($rawVariation['salesPrices'][0], $salesPrice->getData());

        $tag = $variation->getTags()[0];
        $this->assertSame(1, $tag->getId());
        $tagData = $tag->getData();
        unset($tagData['tag']);
        $rawTagData = $rawVariation['tags'][0];
        unset($rawTagData['tag']);
        $this->assertEquals($rawTagData, $tagData);

        $tagContent = $tag->getTagData();
        $this->assertSame(1, $tagContent->getId());
        $this->assertSame('i am a tag', $tagContent->getName());
        $this->assertSame('#ffffff', $tagContent->getColor());
        $tagName = $tagContent->getNames()[0];
        $tagClient = $tagContent->getClients()[0];
        $tagContentData = $tagContent->getData();
        unset($tagContentData['names']);
        unset($tagContentData['clients']);
        $rawTagContent = $rawVariation['tags'][0]['tag'];
        unset($rawTagContent['names']);
        unset($rawTagContent['clients']);
        $this->assertInstanceOf(DateTimeInterface::class, $tagContent->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $tagContent->getUpdatedAt());
        $this->assertEquals($rawTagContent, $tagContentData);

        $this->assertSame(1, $tagName->getId());
        $this->assertSame(1, $tagName->getTagId());
        $this->assertSame('de', $tagName->getLang());
        $this->assertSame('i am a tag', $tagName->getName());
        $this->assertEquals($tagName->getData(), $rawVariation['tags'][0]['tag']['names'][0]);

        $this->assertSame(1, $tagClient->getId());
        $this->assertSame(1, $tagClient->getTagId());
        $this->assertSame(34185, $tagClient->getPlentyId());
        $this->assertInstanceOf(DateTimeInterface::class, $tagClient->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $tagClient->getCreatedAt());
        $this->assertEquals($tagClient->getData(), $rawVariation['tags'][0]['tag']['clients'][0]);

        $image = $variation->getImages()[0];
        $this->assertSame(46, $image->getId());
        $this->assertSame(131, $image->getItemId());
        $this->assertSame('b0f935f9768b95eed376ae53b6e503b7', $image->getMd5Checksum());
        $this->assertSame('b0f935f9768b95eed376ae53b6e503b7', $image->getMd5ChecksumOriginal());
        $this->assertSame(716, $image->getWidth());
        $this->assertSame(557, $image->getHeight());
        $this->assertSame(0, $image->getPosition());
        $this->assertSame(
            'https://cdn03.plentymarkets.com/0pb05rir4h9r/item/images/131/full/' .
            '131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg',
            $image->getUrl()
        );
        $this->assertSame('jpg', $image->getFileType());
        $this->assertSame(
            'https://cdn03.plentymarkets.com/0pb05rir4h9r/item/images/131/' .
            'middle/131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg',
            $image->getUrlMiddle()
        );
        $this->assertTrue($image->hasLinkedVariations());
        $this->assertSame(
            'https://cdn03.plentymarkets.com/0pb05rir4h9r/item/images/' .
            '131/preview/131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg',
            $image->getUrlPreview()
        );
        $this->assertEmpty($image->getAttributeValueImages());
        $this->assertSame('internal', $image->getType());
        $this->assertSame(40783, $image->getSize());
        $this->assertSame(2, $image->getStorageProviderId());
        $this->assertSame($image->getPath(), 'S3:131:131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg');
        $this->assertSame(
            'https://cdn03.plentymarkets.com/0pb05rir4h9r/item/images/' .
            '131/secondPreview/131-Zweisitzer-Amsterdam-at-Dawn-blau.jpg',
            $image->getUrlSecondPreview()
        );
        $this->assertInstanceOf(DateTimeInterface::class, $image->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $image->getCreatedAt());

        $expectedImageData = $rawVariation['images'][0];
        unset($expectedImageData['names']);
        unset($expectedImageData['availabilities']);
        unset($expectedImageData['createdAt']);
        unset($expectedImageData['updatedAt']);

        $actualImageData = $image->getData();
        unset($actualImageData['names']);
        unset($actualImageData['availabilities']);
        unset($actualImageData['createdAt']);
        unset($actualImageData['updatedAt']);

        $this->assertEquals($expectedImageData, $actualImageData);

        $imageName = $image->getNames()[0];
        $this->assertSame(46, $imageName->getId());
        $this->assertSame('de', $imageName->getLang());
        $this->assertSame('', $imageName->getName());
        $this->assertSame('', $imageName->getAlternate());
        $this->assertEquals($rawVariation['images'][0]['names'][0], $imageName->getData());

        $imageAvailability = $image->getAvailabilities()[0];
        $this->assertSame(46, $imageAvailability->getId());
        $this->assertSame('marketplace', $imageAvailability->getType());
        $this->assertSame(139, $imageAvailability->getValue());
        $this->assertEquals($rawVariation['images'][0]['availabilities'][0], $imageAvailability->getData());

        $base = $variation->getBase();
        $this->assertTrue($base->isMain());
        $this->assertNull($base->getMainVariationId());
        $this->assertSame(106, $base->getItemId());
        $this->assertSame(0, $base->getPosition());
        $this->assertTrue($base->isActive());
        $this->assertSame('S-000813-C', $base->getNumber());
        $this->assertSame('modeeeel', $base->getModel());
        $this->assertSame('', $base->getExternalId());
        $this->assertSame(1, $base->getAvailability());
        $this->assertNull($base->getEstimatedAvailableAt());
        $this->assertSame(0.0, $base->getPurchasePrice());
        $this->assertNull($base->getMovingAveragePrice());
        $this->assertNull($base->getPriceCalculationId());
        $this->assertNull($base->getPicking());
        $this->assertSame(1, $base->getStockLimitation());
        $this->assertTrue($base->isVisibleIfNetStockIsPositive());
        $this->assertTrue($base->isInvisibleIfNetStockIsNotPositive());
        $this->assertFalse($base->isAvailableIfNetStockIsPositive());
        $this->assertFalse($base->isUnavailableIfNetStockIsNotPositive());
        $this->assertFalse($base->isVisibleInListIfNetStockIsPositive());
        $this->assertFalse($base->isInvisibleInListIfNetStockIsNotPositive());
        $this->assertSame(1, $base->getMainWarehouseId());
        $this->assertSame(0, $base->getMaximumOrderQuantity());
        $this->assertNull($base->getMinimumOrderQuantity());
        $this->assertNull($base->getIntervalOrderQuantity());
        $this->assertNull($base->getAvailableUntil());
        $this->assertNull($base->getReleasedAt());
        $this->assertNull($base->getName());
        $this->assertSame(0, $base->getWeightG());
        $this->assertSame(0, $base->getWeightNetG());
        $this->assertSame(0, $base->getWidthMM());
        $this->assertSame(0, $base->getLengthMM());
        $this->assertSame(0, $base->getHeightMM());
        $this->assertSame(0.0, $base->getExtraShippingCharge1());
        $this->assertSame(0.0, $base->getExtraShippingCharge2());
        $this->assertSame(1, $base->getUnitsContained());
        $this->assertSame(24, $base->getPalletTypeId());
        $this->assertSame(1, $base->getPackingUnits());
        $this->assertSame(0, $base->getPackingUnitTypeId());
        $this->assertSame(0.0, $base->getTransportationCosts());
        $this->assertSame(0.0, $base->getStorageCosts());
        $this->assertSame(0, $base->getCustoms());
        $this->assertSame(0, $base->getOperatingCosts());
        $this->assertSame(0, $base->getVatId());
        $this->assertNull($base->getBundleType());
        $this->assertSame(0, $base->getAutomaticClientVisibility());
        $this->assertSame(3, $base->getAutomaticListVisibility());
        $this->assertFalse($base->isHiddenInCategoryList());
        $this->assertNull($base->getDefaultShippingCosts());
        $this->assertTrue($base->mayShowUnitPrice());
        $this->assertNull($base->getParentVariationId());
        $this->assertNull($base->getParentVariationQuantity());
        $this->assertNull($base->getSingleItemCount());
        $this->assertNull($base->hasCalculatedBundleWeight());
        $this->assertNull($base->hasCalculatedBundleNetWeight());
        $this->assertNull($base->hasCalculatedBundlePurchasePrice());
        $this->assertNull($base->hasCalculatedBundleMovingAveragePrice());
        $this->assertNull($base->getCustomsTariffNumber());
        $this->assertFalse($base->areCategoriesInherited());
        $this->assertFalse($base->isReferrerInherited());
        $this->assertFalse($base->areClientsInherited());
        $this->assertFalse($base->areSalesPricesInherited());
        $this->assertFalse($base->isSupplierInherited());
        $this->assertFalse($base->areWarehousesInherited());
        $this->assertFalse($base->arePropertiesInherited());
        $this->assertFalse($base->areTagsInherited());

        $expectedBaseData = $rawVariation['base'];
        unset($expectedBaseData['item']);
        unset($expectedBaseData['characteristics']);
        unset($expectedBaseData['images']);

        $actualBaseData = $variation->getBase()->getData();
        unset($actualBaseData['item']);
        unset($actualBaseData['characteristics']);
        unset($actualBaseData['images']);

        $this->assertEquals($expectedBaseData, $actualBaseData);

        $baseItemDetails = $base->getItem();
        $this->assertSame(106, $baseItemDetails->getId());
        $this->assertSame(0, $baseItemDetails->getPosition());
        $this->assertSame('0', $baseItemDetails->getAddCmsPage());
        $this->assertSame(0, $baseItemDetails->getCondition());
        $this->assertNull($baseItemDetails->getFree1());
        $this->assertNull($baseItemDetails->getFree2());
        $this->assertNull($baseItemDetails->getFree3());
        $this->assertNull($baseItemDetails->getFree4());
        $this->assertNull($baseItemDetails->getFree5());
        $this->assertNull($baseItemDetails->getFree6());
        $this->assertNull($baseItemDetails->getFree7());
        $this->assertNull($baseItemDetails->getFree8());
        $this->assertNull($baseItemDetails->getFree9());
        $this->assertNull($baseItemDetails->getFree10());
        $this->assertNull($baseItemDetails->getFree11());
        $this->assertNull($baseItemDetails->getFree12());
        $this->assertNull($baseItemDetails->getFree13());
        $this->assertNull($baseItemDetails->getFree14());
        $this->assertNull($baseItemDetails->getFree15());
        $this->assertNull($baseItemDetails->getFree16());
        $this->assertNull($baseItemDetails->getFree17());
        $this->assertNull($baseItemDetails->getFree18());
        $this->assertNull($baseItemDetails->getFree19());
        $this->assertNull($baseItemDetails->getFree20());
        $this->assertSame('0', $baseItemDetails->getGimahhot());
        $this->assertSame(0, $baseItemDetails->getStoreSpecial());
        $this->assertNull($baseItemDetails->getOwnerId());
        $this->assertSame(2, $baseItemDetails->getManufacturerId());
        $this->assertSame(1, $baseItemDetails->getProducingCountryId());
        $this->assertSame(0.0, $baseItemDetails->getRevenueAccount());
        $this->assertSame(0, $baseItemDetails->getCouponRestriction());
        $this->assertSame(0, $baseItemDetails->getConditionApi());
        $this->assertFalse($baseItemDetails->isSubscribable());
        $this->assertSame(0, $baseItemDetails->getAmazonFbaPlatform());
        $this->assertFalse($baseItemDetails->isShippableByAmazon());
        $this->assertSame(0, $baseItemDetails->getAmazonProductType());
        $this->assertSame('', $baseItemDetails->getAmazonFedas());
        $this->assertNull($baseItemDetails->getEbayPresetId());
        $this->assertNull($baseItemDetails->getEbayCategory());
        $this->assertNull($baseItemDetails->getEbayCategory2());
        $this->assertNull($baseItemDetails->getEbayStoreCategory());
        $this->assertNull($baseItemDetails->getEbayStoreCategory2());
        $this->assertSame(0, $baseItemDetails->getRakutenCategoryId());
        $this->assertSame(26, $baseItemDetails->getFlagOne());
        $this->assertSame(0, $baseItemDetails->getFlagTwo());
        $this->assertSame(0, $baseItemDetails->getAgeRestriction());
        $this->assertSame(0, $baseItemDetails->getFeedback());
        $this->assertSame('default', $baseItemDetails->getItemType());
        $this->assertSame(0, $baseItemDetails->getStockType());
        $this->assertSame('0', $baseItemDetails->getSitemapPublished());
        $this->assertFalse($baseItemDetails->isSerialNumber());
        $this->assertFalse($baseItemDetails->isShippingPackage());
        $this->assertSame(0, $baseItemDetails->getMaximumOrderQuantity());
        $this->assertSame(1, $baseItemDetails->getVariationCount());
        $this->assertSame('', $baseItemDetails->getCustomsTariffNumber());
        $this->assertSame(1004, $baseItemDetails->getMainVariationId());
        $this->assertTrue($baseItemDetails->isInactive());
        $this->assertInstanceOf(DateTimeInterface::class, $baseItemDetails->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $baseItemDetails->getCreatedAt());

        $expectedBaseItem = $rawVariation['base']['item'];
        unset($expectedBaseItem['createdAt']);
        unset($expectedBaseItem['updatedAt']);

        $actualBaseItem = $baseItemDetails->getData();
        unset($actualBaseItem['createdAt']);
        unset($actualBaseItem['updatedAt']);
        $this->assertEquals($expectedBaseItem, $actualBaseItem);

        $unit = $variation->getUnit();
        $this->assertInstanceOf(Unit::class, $unit);

        $this->assertEquals($rawVariation['unit'], $unit->getData());
        $this->assertSame(4, $unit->getUnitId());
        $this->assertSame(321123, $unit->getUnitCombinationId());
        $this->assertSame('1000', $unit->getContent());
    }

    public function testTextCharacteristicDataCanBeFetched(): void
    {
        $response = $this->getMockResponse('Pim/Variations/variation_with_characteristic.json');
        $rawResponse = json_decode($response->getBody()->__toString(), true);
        $variation = PimVariationsParser::parse($response)->first();

        $characteristic = $variation->getBase()->getCharacteristics()[0];
        $this->assertSame(1, $characteristic->getId());
        $this->assertSame(2, $characteristic->getPropertyId());
        $this->assertNull($characteristic->getPropertySelectionId());
        $this->assertSame(106, $characteristic->getItemId());
        $this->assertSame(1004, $characteristic->getVariationId());
        $this->assertSame(0, $characteristic->getSurcharge());
        $this->assertNull($characteristic->getValueFloat());
        $this->assertNull($characteristic->getValueInt());
        $this->assertNull($characteristic->getValueFile());
        $this->assertEmpty($characteristic->getPropertySelections());
        $this->assertInstanceOf(DateTimeInterface::class, $characteristic->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $characteristic->getCreatedAt());

        $expectedCharacteristicData = $rawResponse['entries'][0]['base']['characteristics'][0];
        unset($expectedCharacteristicData['valueTexts']);
        $actualCharacteristicData = $characteristic->getData();
        unset($actualCharacteristicData['valueTexts']);

        $this->assertEquals($expectedCharacteristicData, $actualCharacteristicData);

        $valueTexts = $characteristic->getValueTexts()[0];
        $this->assertSame(1, $valueTexts->getId());
        $this->assertSame('Länge <40 cm', $valueTexts->getValue());
        $this->assertSame('de', $valueTexts->getLang());

        $expectedValueTexts = $rawResponse['entries'][0]['base']['characteristics'][0]['valueTexts'][0];
        $this->assertEquals($expectedValueTexts, $valueTexts->getData());
    }

    public function testSelectionCharacteristicDataCanBeFetched(): void
    {
        $response = $this->getMockResponse('Pim/Variations/variation_with_characteristic_selections.json');
        $rawResponse = json_decode($response->getBody()->__toString(), true);
        $variation = PimVariationsParser::parse($response)->first();

        $characteristic = $variation->getBase()->getCharacteristics()[0];
        $selection = $characteristic->getPropertySelections()[0];

        $this->assertSame(1, $selection->getId());
        $this->assertSame(1, $selection->getPropertyId());
        $this->assertSame('Länge <40 cm', $selection->getName());
        $this->assertSame('de', $selection->getLang());
        $this->assertSame('', $selection->getDescription());

        $expectedSelection = $rawResponse['entries'][0]['base']['characteristics'][0]['propertySelection'][0];
        $this->assertEquals($expectedSelection, $selection->getData());
    }

    public function testPropertyDataCanBeFetched(): void
    {
        $response = $this->getMockResponse('Pim/Variations/variation_with_properties.json');
        $rawResponse = json_decode($response->getBody()->__toString(), true);
        $variation = PimVariationsParser::parse($response)->first();
        $rawProperty = $rawResponse['entries'][0]['properties'][0];

        $property = $variation->getProperties()[0];
        $this->assertSame(11, $property->getId());

        $expectedProperty = $rawProperty;
        unset($expectedProperty['values']);
        unset($expectedProperty['property']);
        $actualProperty = $property->getData();
        unset($actualProperty['values']);
        unset($actualProperty['property']);

        $this->assertEquals($expectedProperty, $actualProperty);

        $propertyValue = $property->getValues()[0];
        $this->assertNull($propertyValue->getId());
        $this->assertSame('0', $propertyValue->getLang());
        $this->assertSame('100', $propertyValue->getValue());
        $this->assertNull($propertyValue->getDescription());

        $expectedPropertyValue = $rawProperty['values'][0];
        $this->assertEquals($expectedPropertyValue, $propertyValue->getData());

        $propertyData = $property->getPropertyData();
        $this->assertSame(11, $propertyData->getId());
        $this->assertSame(7, $propertyData->getPosition());
        $this->assertSame('item', $propertyData->getType());
        $this->assertSame('float', $propertyData->getCast());
        $this->assertInstanceOf(DateTimeInterface::class, $propertyData->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $propertyData->getCreatedAt());

        $expectedPropertyData = $rawProperty['property'];
        unset($expectedPropertyData['options']);
        unset($expectedPropertyData['names']);
        $actualPropertyData = $propertyData->getData();
        unset($actualPropertyData['options']);
        unset($actualPropertyData['names']);

        $this->assertEquals($expectedPropertyData, $actualPropertyData);

        $option = $propertyData->getOptions()[0];
        $this->assertSame(17, $option->getId());
        $this->assertSame(11, $option->getPropertyId());
        $this->assertSame('clients', $option->getTypeOptionIdentifier());
        $this->assertInstanceOf(DateTimeInterface::class, $option->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $option->getCreatedAt());

        $expectedOption = $rawProperty['property']['options'][0];
        unset($expectedOption['propertyOptionValues']);
        $actualOption = $option->getData();
        unset($actualOption['propertyOptionValues']);

        $this->assertEquals($expectedOption, $actualOption);

        $optionValue = $option->getValues()[0];
        $this->assertSame(17, $optionValue->getId());
        $this->assertSame(17, $optionValue->getOptionId());
        $this->assertSame('34185', $optionValue->getValue());
        $this->assertInstanceOf(DateTimeInterface::class, $optionValue->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $optionValue->getCreatedAt());

        $expectedOptionValue = $rawProperty['property']['options'][0]['propertyOptionValues'][0];
        $this->assertEquals($expectedOptionValue, $optionValue->getData());

        $propertyName = $propertyData->getNames()[0];
        $this->assertSame(11, $propertyName->getId());
        $this->assertSame(11, $propertyName->getPropertyId());
        $this->assertSame('de', $propertyName->getLang());
        $this->assertSame('Bereichsslider', $propertyName->getValue());
        $this->assertSame('', $propertyName->getDescription());
        $this->assertInstanceOf(DateTimeInterface::class, $propertyName->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $propertyName->getCreatedAt());

        $expectedPropertyName = $rawProperty['property']['names'][0];
        $this->assertEquals($expectedPropertyName, $propertyName->getData());
    }

    public function testPropertiesWithRelationsCanBeFetched(): void
    {
        $response = $this->getMockResponse('Pim/Variations/variation_with_multi_selection_properties.json');
        $rawResponse = json_decode($response->getBody()->__toString(), true);
        $variation = PimVariationsParser::parse($response)->first();
        $rawProperty = $rawResponse['entries'][0]['properties'][0];
        $property = $variation->getProperties()[0];

        $selection = $property->getPropertyData()->getSelections()[0];
        $this->assertSame(7, $selection->getId());
        $this->assertSame(7, $selection->getPropertyId());
        $this->assertSame(0, $selection->getPosition());
        $this->assertInstanceOf(DateTimeInterface::class, $selection->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $selection->getCreatedAt());

        $expectedSelection = $rawProperty['property']['selections'][0];
        unset($expectedSelection['relation']);
        $actualSelection = $selection->getData();
        unset($actualSelection['relation']);

        $this->assertEquals($expectedSelection, $actualSelection);

        $relation = $selection->getRelation();
        $this->assertSame(27, $relation->getId());
        $this->assertSame(7, $relation->getPropertyId());
        $this->assertNull($relation->getRelationTargetId());
        $this->assertNull($relation->getRelationTypeIdentifier());
        $this->assertSame(7, $relation->getSelectionRelationId());
        $this->assertInstanceOf(DateTimeInterface::class, $relation->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $relation->getCreatedAt());

        $expectedRelation = $rawProperty['property']['selections'][0]['relation'];
        unset($expectedRelation['relationValues']);
        $actualRelation = $relation->getData();
        unset($actualRelation['relationValues']);

        $this->assertEquals($expectedRelation, $actualRelation);

        $relationValue = $relation->getValues()[0];
        $this->assertSame(24, $relationValue->getId());
        $this->assertSame(12, $relationValue->getRelationId());
        $this->assertSame('BAAM', $relationValue->getValue());
        $this->assertSame('DE', $relationValue->getLang());
        $this->assertNull($relationValue->getDescription());
        $this->assertInstanceOf(DateTimeInterface::class, $relationValue->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $relationValue->getCreatedAt());

        $expectedRelationValue = $rawProperty['property']['selections'][0]['relation']['relationValues'][0];
        $this->assertEquals($expectedRelationValue, $relationValue->getData());
    }
}
