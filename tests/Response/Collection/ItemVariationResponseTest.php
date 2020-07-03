<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemVariationParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\Parser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\itemVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\Tag\Tag\Name as TagName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationAttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationAttributeValue\AttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationCategory;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationClient;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationBarcode;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationSalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\VariationTag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection\Property as SelectionProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class ItemVariationResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $itemVariationResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('ItemVariationResponse/response.json');
        $this->itemVariationResponse = ItemVariationParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'number' => 'S-000813-C'
                ],
                'expectedId' => 1001
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'variationProperties' => [
                        'id' => 67
                    ]
                ],
                'expectedId' => 1002
            ],
            'criteria with multi-level sub-criteria' => [
                'criteria' => [
                    'itemImages' => [
                        'names' => [
                            'lang' => 'de',
                            'name' => 'aaaaeeeeeee'
                        ]
                    ]
                ],
                'expectedId' => 1002
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'itemId' => 105,
                    'itemImages' => [
                        'path' => 'S3:105:105-buerostuhl-schwarz.jpg',
                        'availabilities' => [
                            'imageId' => 13
                        ]
                    ],
                    'variationClients' => [
                        'variationId' => 1003,
                        'plentyId' => 37811
                    ]
                ],
                'expectedId' => 1003
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $salesPrice = $this->itemVariationResponse->findOne($criteria);

        $this->assertEquals($expectedId, $salesPrice->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(4, $this->itemVariationResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'widthMM' => 100
        ];

        self::assertCount(2, $this->itemVariationResponse->find($criteria));
    }

    public function testItemVariationDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $itemVariations = $this->itemVariationResponse->all();
        foreach ($itemVariations as $key => $itemVariation) {
            $this->assertEquals($responseData['entries'][$key], $itemVariation->getData());
        }
        /** @var ItemVariation $itemVariation */
        $itemVariation = $this->itemVariationResponse->first();

        $this->assertEquals(1000, $itemVariation->getId());
        $this->assertEquals(true, $itemVariation->isMain());
        $this->assertEquals(null, $itemVariation->getMainVariationId());
        $this->assertEquals(102, $itemVariation->getItemId());
        $this->assertEquals(2000, $itemVariation->getCategoryVariationId());
        $this->assertEquals(3000, $itemVariation->getMarketVariationId());
        $this->assertEquals(4000, $itemVariation->getClientVariationId());
        $this->assertEquals(5000, $itemVariation->getSalesPriceVariationId());
        $this->assertEquals(6000, $itemVariation->getSupplierVariationId());
        $this->assertEquals(7000, $itemVariation->getWarehouseVariationId());
        $this->assertEquals(0, $itemVariation->getPosition());
        $this->assertEquals(true, $itemVariation->isActive());
        $this->assertEquals('S-005951-X', $itemVariation->getNumber());
        $this->assertEquals('model', $itemVariation->getModel());
        $this->assertEquals('externalId', $itemVariation->getExternalId());
        $this->assertEquals(123, $itemVariation->getParentVariationId());
        $this->assertEquals(10.12, $itemVariation->getParentVariationQuantity());
        $this->assertEquals(5, $itemVariation->getAvailability());
        $this->assertEquals('2042-01-01 00:00:00', $itemVariation->getEstimatedAvailableAt());
        $this->assertEquals(0, $itemVariation->getPurchasePrice());
        $this->assertEquals('2014-12-23T23:00:00+00:00', $itemVariation->getCreatedAt());
        $this->assertEquals('2019-02-22T10:46:08+00:00', $itemVariation->getUpdatedAt());
        $this->assertEquals('2019-02-25T08:08:10+00:00', $itemVariation->getRelatedUpdatedAt());
        $this->assertEquals(1234, $itemVariation->getPriceCalculationId());
        $this->assertEquals('picking', $itemVariation->getPicking());
        $this->assertEquals(1, $itemVariation->getStockLimitation());
        $this->assertEquals(true, $itemVariation->IsVisibleIfNetStockIsPositive());
        $this->assertEquals(true, $itemVariation->IsInvisibleIfNetStockIsNotPositive());
        $this->assertEquals(false, $itemVariation->IsAvailableIfNetStockIsPositive());
        $this->assertEquals(false, $itemVariation->IsUnavailableIfNetStockIsNotPositive());
        $this->assertEquals(1, $itemVariation->getMainWarehouseId());
        $this->assertEquals(14.44, $itemVariation->getMaximumOrderQuantity());
        $this->assertEquals(15.55, $itemVariation->getMinimumOrderQuantity());
        $this->assertEquals(16.66, $itemVariation->getIntervalOrderQuantity());
        $this->assertEquals('2042-04-21 00:00:00', $itemVariation->getAvailableUntil());
        $this->assertEquals('2040-01-01 00:00:00', $itemVariation->getReleasedAt());
        $this->assertEquals(55, $itemVariation->getUnitCombinationId());
        $this->assertEquals('name', $itemVariation->getName());
        $this->assertEquals(1, $itemVariation->getWeightG());
        $this->assertEquals(2, $itemVariation->getWeightNetG());
        $this->assertEquals(3, $itemVariation->getWidthMM());
        $this->assertEquals(4, $itemVariation->getLengthMM());
        $this->assertEquals(5, $itemVariation->getHeightMM());
        $this->assertEquals(6, $itemVariation->getExtraShippingCharge1());
        $this->assertEquals(7, $itemVariation->getExtraShippingCharge2());
        $this->assertEquals(8, $itemVariation->getUnitsContained());
        $this->assertEquals(9, $itemVariation->getPalletTypeId());
        $this->assertEquals(10, $itemVariation->getPackingUnits());
        $this->assertEquals(11, $itemVariation->getPackingUnitTypeId());
        $this->assertEquals(12, $itemVariation->getTransportationCosts());
        $this->assertEquals(13, $itemVariation->getStorageCosts());
        $this->assertEquals(14, $itemVariation->getCustoms());
        $this->assertEquals(15, $itemVariation->getOperatingCosts());
        $this->assertEquals(16, $itemVariation->getVatId());
        $this->assertEquals('bundleType', $itemVariation->getBundleType());
        $this->assertEquals(0, $itemVariation->getAutomaticClientVisibility());
        $this->assertEquals(false, $itemVariation->IsHiddenInCategoryList());
        $this->assertEquals(0.99, $itemVariation->getDefaultShippingCosts());
        $this->assertEquals(true, $itemVariation->getMayShowUnitPrice());
        $this->assertEquals(1000, $itemVariation->getMovingAveragePrice());
        $this->assertEquals('1001', $itemVariation->getPropertyVariationId());
        $this->assertEquals(3, $itemVariation->getAutomaticListVisibility());
        $this->assertEquals(false, $itemVariation->isVisibleInListIfNetStockIsPositive());
        $this->assertEquals(false, $itemVariation->isInvisibleInListIfNetStockIsNotPositive());
        $this->assertEquals(0, $itemVariation->getSingleItemCount());
        $this->assertEquals('2019-02-22T10:46:08+00:00', $itemVariation->getAvailabilityUpdatedAt());
        $this->assertEquals('1000', $itemVariation->getTagVariationId());
        $this->assertEquals(true, $itemVariation->getHasCalculatedBundleWeight());
        $this->assertEquals(false, $itemVariation->getHasCalculatedBundleNetWeight());
        $this->assertEquals(true, $itemVariation->getHasCalculatedBundlePurchasePrice());
        $this->assertEquals(false, $itemVariation->getHasCalculatedBundleMovingAveragePrice());
        $this->assertEquals(4, $itemVariation->getSalesRank());

        $variationCategories = $itemVariation->getVariationCategories();
        $this->assertIsArray($variationCategories);
        $this->assertCount(1, $variationCategories);
        /** @var VariationCategory $variationCategory */
        $variationCategory = reset($variationCategories);
        $this->assertEquals(1000, $variationCategory->getVariationId());
        $this->assertEquals(17, $variationCategory->getCategoryId());
        $this->assertEquals(0, $variationCategory->getPosition());
        $this->assertEquals(true, $variationCategory->isNeckermannPrimary());

        $variationSalesPrices = $itemVariation->getVariationSalesPrices();
        $this->assertIsArray($variationSalesPrices);
        $this->assertCount(2, $variationSalesPrices);
        /** @var VariationSalesPrice $variationSalesPrice */
        $variationSalesPrice = reset($variationSalesPrices);
        $this->assertEquals(1000, $variationSalesPrice->getVariationId());
        $this->assertEquals(1, $variationSalesPrice->getSalesPriceId());
        $this->assertEquals(269.99, $variationSalesPrice->getPrice());
        $this->assertEquals('2016-09-05T12:25:20+01:00', $variationSalesPrice->getCreatedAt());
        $this->assertEquals('2016-09-05T12:25:20+01:00', $variationSalesPrice->getUpdatedAt());

        $variationAttributeValues = $itemVariation->getVariationAttributeValues();
        $this->assertIsArray($variationAttributeValues);
        $this->assertCount(1, $variationAttributeValues);
        /** @var VariationAttributeValue $variationAttributeValue */
        $variationAttributeValue = reset($variationAttributeValues);
        $this->assertEquals(1, $variationAttributeValue->getAttributeValueSetId());
        $this->assertEquals(1, $variationAttributeValue->getAttributeId());
        $this->assertEquals(1, $variationAttributeValue->getValueId());
        $this->assertEquals(false, $variationAttributeValue->isLinkableToImage());

        /** @var Attribute $attribute */
        $attribute = $variationAttributeValue->getAttribute();
        $this->assertEquals(1, $attribute->getId());
        $this->assertEquals('Couch color', $attribute->getBackendName());
        $this->assertEquals(1, $attribute->getPosition());
        $this->assertEquals(false, $attribute->isSurchargePercental());
        $this->assertEquals(true, $attribute->isLinkableToImage());
        $this->assertEquals('amazonAttribute', $attribute->getAmazonAttribute());
        $this->assertEquals('color', $attribute->getFruugoAttribute());
        $this->assertEquals(0, $attribute->getPixmaniaAttribute());
        $this->assertEquals('ottoAttribute', $attribute->getOttoAttribute());
        $this->assertEquals('', $attribute->getGoogleShoppingAttribute());
        $this->assertEquals(0, $attribute->getNeckermannAtEpAttribute());
        $this->assertEquals('dropdown', $attribute->getTypeOfSelectionInOnlineStore());
        $this->assertEquals(0, $attribute->getLaRedouteAttribute());
        $this->assertEquals(false, $attribute->isGroupable());
        $this->assertEquals('2015-04-30T08:56:34+01:00', $attribute->getUpdatedAt());

        /** @var AttributeValue $attributeValue */
        $attributeValue = $variationAttributeValue->getAttributeValue();
        $this->assertEquals(1, $attributeValue->getId());
        $this->assertEquals(1, $attributeValue->getAttributeId());
        $this->assertEquals('black', $attributeValue->getBackendName());
        $this->assertEquals(1, $attributeValue->getPosition());
        $this->assertEquals('image', $attributeValue->getImage());
        $this->assertEquals('comment', $attributeValue->getComment());
        $this->assertEquals('amazonValue', $attributeValue->getAmazonValue());
        $this->assertEquals('ottoValue', $attributeValue->getOttoValue());
        $this->assertEquals('neckermannAtEpValue', $attributeValue->getNeckermannAtEpValue());
        $this->assertEquals('laRedouteValue', $attributeValue->getLaRedouteValue());
        $this->assertEquals('tracdelightValue', $attributeValue->getTracdelightValue());
        $this->assertEquals(0, $attributeValue->getPercentageDistribution());
        $this->assertEquals('2014-01-14T23:32:01+00:00', $attributeValue->getUpdatedAt());

        $variationProperties = $itemVariation->getVariationProperties();
        $this->assertIsArray($variationProperties);
        $this->assertCount(3, $variationProperties);
        /** @var VariationProperty $variationProperty */
        $variationProperty = reset($variationProperties);
        $this->assertEquals(4, $variationProperty->getId());
        $this->assertEquals(102, $variationProperty->getItemId());
        $this->assertEquals(1, $variationProperty->getPropertyId());
        $this->assertEquals(null, $variationProperty->getPropertySelectionId());
        $this->assertEquals(123, $variationProperty->getValueInt());
        $this->assertEquals(321.12, $variationProperty->getValueFloat());
        $this->assertEquals(null, $variationProperty->getValueFile());
        $this->assertEquals(0.0, $variationProperty->getSurcharge());
        $this->assertEquals('2018-06-12T15:31:14+01:00', $variationProperty->getUpdatedAt());
        $this->assertEquals('2018-06-12T15:31:14+01:00', $variationProperty->getCreatedAt());
        $this->assertEquals(1000, $variationProperty->getVariationId());
        $this->assertEquals([], $variationProperty->getNames());
        $this->assertEquals([], $variationProperty->getPropertySelection());

        /** @var ItemProperty $property */
        $property = $variationProperty->getProperty();
        $this->assertEquals(1, $property->getId());
        $this->assertEquals(0, $property->getPosition());
        $this->assertEquals(null, $property->getPropertyGroupId());
        $this->assertEquals('KGM', $property->getUnit());
        $this->assertEquals('Test', $property->getBackendName());
        $this->assertEquals('', $property->getComment());
        $this->assertEquals('int', $property->getValueType());
        $this->assertEquals(true, $property->isSearchable());
        $this->assertEquals(false, $property->isOderProperty());
        $this->assertEquals(true, $property->isShownOnItemPage());
        $this->assertEquals(true, $property->isShownOnItemList());
        $this->assertEquals(true, $property->isShownAtCheckout());
        $this->assertEquals(true, $property->isShownInPdf());
        $this->assertEquals(false, $property->isShownAsAdditionalCosts());
        $this->assertEquals(0.0, $property->getSurcharge());
        $this->assertEquals('2019-02-22T13:57:13+00:00', $property->getUpdatedAt());

        $variationBarcodes = $itemVariation->getVariationBarcodes();
        $this->assertIsArray($variationBarcodes);
        $this->assertCount(1, $variationBarcodes);
        /** @var VariationBarcode $variationBarcode */
        $variationBarcode = reset($variationBarcodes);
        $this->assertEquals(1000, $variationBarcode->getVariationId());
        $this->assertEquals(0, $variationBarcode->getBarcodeId());
        $this->assertEquals('1231231231231', $variationBarcode->getCode());
        $this->assertEquals('2016-09-05T12:25:16+01:00', $variationBarcode->getCreatedAt());

        $variationClients = $itemVariation->getVariationClients();
        $this->assertIsArray($variationClients);
        $this->assertCount(1, $variationClients);
        /** @var VariationClient $variationClient */
        $variationClient = reset($variationClients);
        $this->assertEquals(1000, $variationClient->getVariationId());
        $this->assertEquals(37811, $variationClient->getPlentyId());
        $this->assertEquals('2016-09-05T12:25:16+01:00', $variationClient->getCreatedAt());

        $properties = $itemVariation->getProperties();
        $this->assertIsArray($properties);
        $this->assertCount(1, $properties);
        /** @var Property $property */
        $property = reset($properties);
        /** @var SelectionProperty $propertyRelation */
        $propertyRelation = $property->getPropertyRelation();
        $this->assertEquals(6, $propertyRelation->getId());
        $this->assertEquals('shortText', $propertyRelation->getCast());
        $this->assertEquals('item', $propertyRelation->getTypeIdentifier());
        $this->assertEquals(50, $propertyRelation->getPosition());
        $this->assertEquals('2019-02-22T14:54:23+00:00', $propertyRelation->getCreatedAt());
        $this->assertEquals('2019-02-22T14:54:23+00:00', $propertyRelation->getUpdatedAt());

        $itemImages = $itemVariation->getItemImages();
        $this->assertIsArray($itemImages);
        $this->assertCount(1, $itemImages);
        /** @var ItemImage $itemImage */
        $itemImage = reset($itemImages);
        $this->assertEquals(19, $itemImage->getId());
        $this->assertEquals(102, $itemImage->getItemId());
        $this->assertEquals('internal', $itemImage->getType());
        $this->assertEquals('jpg', $itemImage->getFileType());
        $this->assertEquals('S3:102:102-sessel-leder-gruen.jpg', $itemImage->getPath());
        $this->assertEquals(0, $itemImage->getPosition());
        $this->assertEquals('2019-02-22 10:46:08', $itemImage->getLastUpdate());
        $this->assertEquals('2013-12-09 14:34:48', $itemImage->getInsert());
        $this->assertEquals('', $itemImage->getMd5Checksum());
        $this->assertEquals(0.0, $itemImage->getWidth());
        $this->assertEquals(0.0, $itemImage->getHeight());
        $this->assertEquals(0.0, $itemImage->getSize());
        $this->assertEquals('2', $itemImage->getStorageProviderId());
        $this->assertEquals('', $itemImage->getMd5ChecksumOriginal());
        $this->assertEquals('102-sessel-leder-gruen.jpg', $itemImage->getCleanImageName());
        $this->assertEquals('https://images.com/full/image.jpg', $itemImage->getUrl());
        $this->assertEquals('https://images.com/middle/image.jpg', $itemImage->getUrlMiddle());
        $this->assertEquals('https://images.com/preview/image.jpg', $itemImage->getUrlPreview());
        $this->assertEquals('https://images.com/secondPreview/image.jpg', $itemImage->getUrlSecondPreview());
        $this->assertEquals('v3b53of2xcyu/item/images/documentupload.jpg', $itemImage->getDocumentUploadPath());
        $this->assertEquals('v3b53of2xcyu/item/images/documentpreview.jpg', $itemImage->getDocumentUploadPathPreview());
        $this->assertEquals(0.0, $itemImage->getDocumentUploadPreviewWidth());
        $this->assertEquals(0.0, $itemImage->getDocumentUploadPreviewHeight());

        $availabilities = $itemImage->getAvailabilities();
        $this->assertIsArray($availabilities);
        $this->assertCount(3, $availabilities);
        /** @var Availability $availability */
        $availability = reset($availabilities);
        $this->assertEquals(19, $availability->getImageId());
        $this->assertEquals('mandant', $availability->getType());
        $this->assertEquals('37811', $availability->getValue());

        $names = $itemImage->getNames();
        $this->assertIsArray($names);
        $this->assertCount(2, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(19, $name->getImageId());
        $this->assertEquals('de', $name->getLang());
        $this->assertEquals('name', $name->getName());
        $this->assertEquals('alt', $name->getAlternate());

        $variationTags = $itemVariation->getTags();
        $this->assertIsArray($variationTags);
        $this->assertCount(3, $variationTags);
        /** @var VariationTag $variationTag */
        $variationTag = reset($variationTags);
        $this->assertEquals('1', $variationTag->getTagId());
        $this->assertEquals('variation', $variationTag->getTagType());
        $this->assertEquals('1011', $variationTag->getRelationshipValue());
        $this->assertEquals('', $variationTag->getRelationshipUUID5());
        $this->assertEquals('2019-04-30T14:37:00+01:00', $variationTag->getCreatedAt());
        $this->assertEquals('2019-04-30T14:37:00+01:00', $variationTag->getUpdatedAt());

        /** @var Tag $tag */
        $tag = $variationTag->getTag();
        $this->assertEquals(1, $tag->getId());
        $this->assertEquals('aaaa', $tag->getTagName());
        $this->assertEquals(null, $tag->getColor());
        $this->assertEquals('2019-04-30T14:33:08+01:00', $tag->getCreatedAt());
        $this->assertEquals('2020-03-03T12:27:50+00:00', $tag->getUpdatedAt());

        $names = $tag->getNames();
        $this->assertIsArray($names);
        $this->assertCount(5, $names);
        /** @var TagName $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getId());
        $this->assertEquals(1, $name->getTagId());
        $this->assertEquals('de', $name->getTagLang());
        $this->assertEquals('Germany First Tage', $name->getTagName());
    }
}
