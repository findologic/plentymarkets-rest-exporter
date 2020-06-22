<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\ItemVariationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ConfigHelper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use Log4Php\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ItemVariationRequestTest extends TestCase
{
    use ConfigHelper;

    /** @var Config */
    private $defaultConfig;

    /** @var Logger|MockObject */
    private $loggerMock;

    /** @var Client */
    private $client;

    /** @var GuzzleClient|MockObject */
    private $guzzleClientMock;

    /** @var ResponseInterface|MockObject */
    private $responseMock;

    public function setUp(): void
    {
        $this->defaultConfig = $this->getDefaultConfig();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->guzzleClientMock = $this->getMockBuilder(GuzzleClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseBodyMock = $this->getMockBuilder(StreamInterface::class)->getMock();
        $responseBodyMock->method('__toString')->willReturn('{"accessToken":"111","refreshToken":true}');
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('getBody')->willReturn($responseBodyMock);
        $this->client = new Client($this->guzzleClientMock, $this->defaultConfig, $this->loggerMock, $this->loggerMock);
    }

    public function testParamsGetSanitizedCorrectly()
    {
        $request = new ItemVariationRequest();
        $request->setWith(['a', 'b', 'c'])
            ->setIsActive(true)
            ->setLang('laaaaaaaang')
            ->setId([123, 321])
            ->setItemId([654, 456])
            ->setVariationTagId([888])
            ->setItemName('naaaaaaame')
            ->setFlagOne('f1')
            ->setFlagTwo('f2')
            ->setStoreSpecial(1)
            ->setCategoryId(987)
            ->setIsMain(false)
            ->setBarcode('123123123')
            ->setNumberExact('54654')
            ->setNumberFuzzy('564654')
            ->setIsBundle(true)
            ->setPlentyId(654)
            ->setReferrerId(111)
            ->setSupplierNumber('222')
            ->setSku('456-456')
            ->setManufacturerId(444)
            ->setUpdatedBetween('2222266666+58+9+8')
            ->setCreatedBetween('78789454654')
            ->setRelatedUpdatedBetween('888888aaaaaaaa')
            ->setItemDescription('daskreebshum')
            ->setStockWarehouseId('4564')
            ->setSupplierId(7987);

        $uri = new Uri('https://plenty-testshop.de/rest/items/variations');
        $modifiedRequest = $request->withUri($uri)->withAddedHeader('Authorization', 'Bearer 111');

        $expectedParams = [
            'http_errors' => false,
            'allow_redirects' => true,
            'query' => [
                'with' => 'a,b,c',
                'isActive' => true,
                'lang' => 'laaaaaaaang',
                'id' => '123,321',
                'itemId' => '654,456',
                'variationTagId' => '888',
                'itemName' => 'naaaaaaame',
                'flagOne' => 'f1',
                'flagTwo' => 'f2',
                'storeSpecial' => 1,
                'categoryId' => 987,
                'isMain' => false,
                'barcode' => '123123123',
                'numberExact' => '54654',
                'numberFuzzy' => '564654',
                'isBundle' => true,
                'plentyId' => 654,
                'referrerId' => 111,
                'supplierNumber' => '222',
                'sku' => '456-456',
                'manufacturerId' => 444,
                'updatedBetween' => '2222266666+58+9+8',
                'createdBetween' => '78789454654',
                'relatedUpdatedBetween' => '888888aaaaaaaa',
                'itemDescription' => 'daskreebshum',
                'stockWarehouseId' => '4564',
                'supplierId' => 7987,
                'page' => 1
            ]
        ];

        $this->guzzleClientMock->method('send')->willReturn($this->responseMock);
        $this->guzzleClientMock->expects($this->at(1))->method('send')->with($modifiedRequest, $expectedParams);
        $this->client->send($request);
    }
}
