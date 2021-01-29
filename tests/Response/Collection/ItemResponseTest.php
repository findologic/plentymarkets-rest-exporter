<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\ItemParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class ItemResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    /** @var ItemResponse */
    private $itemResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('ItemResponse/response.json');
        $this->itemResponse = ItemParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'mainVariationId' => 1004
                ],
                'mainVariationId' => 106
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'texts' => [
                        'shortDescription' => 'Swivel office chair'
                    ]
                ],
                'expectedId' => 105
            ],
            'criteria with multiple sub-criteria' => [
                'criteria' => [
                    'texts' => [
                        'urlPath' => 'leather-sofa-san-jose-brown',
                        'shortDescription' => 'Elegant padded furniture made of real leather'
                    ]
                ],
                'expectedId' => 104
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'mainVariationId' => 1001,
                    'texts' => [
                        'lang' => 'en',
                        'urlPath' => 'brown-armchair-new-york-with-real-leather-upholstery'
                    ]
                ],
                'expectedId' => 103
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $item = $this->itemResponse->findOne($criteria);

        $this->assertEquals($expectedId, $item->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(6, $this->itemResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'manufacturerId' => 4
        ];

        self::assertCount(3, $this->itemResponse->find($criteria));
    }

    public function testDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $items = $this->itemResponse->all();
        foreach ($items as $key => $item) {
            $this->assertEqualsCanonicalizing($responseData['entries'][$key], $item->getData());
        }

        $item = $this->itemResponse->first();
        $this->assertEquals(102, $item->getId());
        $this->assertEquals(100, $item->getPosition());
        $this->assertEquals(2, $item->getManufacturerId());
        $this->assertEquals(0, $item->getStockType());
        $this->assertEquals('0', $item->getAddCmsPage());
        $this->assertEquals(0, $item->getStoreSpecial());
        $this->assertEquals(1, $item->getCondition());
        $this->assertEquals('', $item->getAmazonFedas());
        $this->assertEquals('2019-02-25T08:08:10+00:00', $item->getUpdatedAt());
        $this->assertEquals('1', $item->getFree1());
        $this->assertEquals('2', $item->getFree2());
        $this->assertEquals('3', $item->getFree3());
        $this->assertEquals('4', $item->getFree4());
        $this->assertEquals('5', $item->getFree5());
        $this->assertEquals('6', $item->getFree6());
        $this->assertEquals('7', $item->getFree7());
        $this->assertEquals('8', $item->getFree8());
        $this->assertEquals('9', $item->getFree9());
        $this->assertEquals('10', $item->getFree10());
        $this->assertEquals('11', $item->getFree11());
        $this->assertEquals('12', $item->getFree12());
        $this->assertEquals('13', $item->getFree13());
        $this->assertEquals('14', $item->getFree14());
        $this->assertEquals('15', $item->getFree15());
        $this->assertEquals('16', $item->getFree16());
        $this->assertEquals('17', $item->getFree17());
        $this->assertEquals('18', $item->getFree18());
        $this->assertEquals('19', $item->getFree19());
        $this->assertEquals(null, $item->getFree20());
        $this->assertEquals('customsTariffNumber', $item->getCustomsTariffNumber());
        $this->assertEquals(1, $item->getProducingCountryId());
        $this->assertEquals(213, $item->getRevenueAccount());
        $this->assertEquals(5, $item->getCouponRestriction());
        $this->assertEquals(2, $item->getFlagOne());
        $this->assertEquals(3, $item->getFlagTwo());
        $this->assertEquals(1, $item->getAgeRestriction());
        $this->assertEquals('2014-12-24T00:00:00+00:00', $item->getCreatedAt());
        $this->assertEquals(456, $item->getAmazonProductType());
        $this->assertEquals(10, $item->getEbayPresetId());
        $this->assertEquals(11, $item->getEbayCategory());
        $this->assertEquals(null, $item->getEbayCategory2());
        $this->assertEquals(12, $item->getEbayStoreCategory());
        $this->assertEquals(null, $item->getEbayStoreCategory2());
        $this->assertEquals(0, $item->getAmazonFbaPlatform());
        $this->assertEquals(5.0, $item->getFeedback());
        $this->assertEquals('0', $item->getGimahhot());
        $this->assertEquals(1000.0, $item->getMaximumOrderQuantity());
        $this->assertEquals(false, $item->isSubscribable());
        $this->assertEquals(123, $item->getRakutenCategoryId());
        $this->assertEquals(false, $item->isShippingPackage());
        $this->assertEquals(321, $item->getConditionApi());
        $this->assertEquals(false, $item->isSerialNumber());
        $this->assertEquals(false, $item->isShippableByAmazon());
        $this->assertEquals(null, $item->getOwnerId());
        $this->assertEquals('default', $item->getItemType());
        $this->assertEquals(1000, $item->getMainVariationId());

        $texts = $item->getTexts();
        $this->assertCount(1, $texts);
        /** @var Text $text */
        $text = reset($texts);
        $this->assertEquals('en', $text->getLang());
        $this->assertEquals('1', $text->getName1());
        $this->assertEquals('2', $text->getName2());
        $this->assertEquals('3', $text->getName3());
        $this->assertEquals('shortdescription', $text->getShortDescription());
        $this->assertEquals('metadescription', $text->getMetaDescription());
        $this->assertEquals('description', $text->getDescription());
        $this->assertEquals('techdata', $text->getTechnicalData());
        $this->assertEquals('urlpath', $text->getUrlPath());
        $this->assertEquals('keywords', $text->getKeywords());
    }
}
