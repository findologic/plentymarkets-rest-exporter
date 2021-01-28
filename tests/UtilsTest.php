<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    use ResponseHelper;

    private const TEMP_PATH = '/tmp/rest-export';
    private const CONFIG_PATH = self::TEMP_PATH . '/config.yml';
    private const VALID_SHOPKEY = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    /** @var Client|MockObject */
    private $clientMock;

    public function setUp(): void
    {
        mkdir(self::TEMP_PATH, 0777, true);

        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
    }

    public function tearDown(): void
    {
        if (file_exists(self::TEMP_PATH)) {
            exec('rm -R ' . self::TEMP_PATH);
        }
    }

    public function validShopkeyProvider(): array
    {
        return [
            ['shopkey' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'],
            ['shopkey' => 'BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB'],
            ['shopkey' => '85AC62C6EE528CADABDDB18F9F3D2A99'],
        ];
    }

    /**
     * @dataProvider validShopkeyProvider
     */
    public function testShopkeyIsReturnedIfValid(string $shopkey): void
    {
        $this->assertSame($shopkey, Utils::validateAndGetShopkey($shopkey));
    }

    public function emptyShopkeyProvider(): array
    {
        return [
            ['shopkey' => ''],
            ['shopkey' => null],
        ];
    }

    /**
     * @dataProvider emptyShopkeyProvider
     */
    public function testEmptyShopkeyReturnsNull(?string $shopkey): void
    {
        $this->assertNull(Utils::validateAndGetShopkey($shopkey));
    }

    public function invalidShopkeyProvider(): array
    {
        return [
            ['shopkey' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAR'],
            ['shopkey' => 'LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL'],
            ['shopkey' => '85AC62C6EE528CADABDDB18F9F3D2A9'],
            ['shopkey' => ' '],
            ['shopkey' => '   '],
            ['shopkey' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA '],
        ];
    }

    /**
     * @dataProvider invalidShopkeyProvider
     */
    public function testShopkeyValidationThrowsExceptionInCaseItIsInvalid(?string $shopkey): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given shopkey does not match the shopkey format.');

        Utils::validateAndGetShopkey($shopkey);
    }

    public function configDoesNotCallCustomerLoginProvider(): array
    {
        $expectedDomain = 'blubbergurken.io';

        return [
            'config has no customerLoginUri set' => [
                'customerLoginUri' => null,
                'shopkey' => self::VALID_SHOPKEY,
                'expectedDomain' => $expectedDomain,
            ],
            'config has customerLoginUri set, but no shopkey provided' => [
                'customerLoginUri' => 'https://customer-login.com',
                'shopkey' => null,
                'expectedDomain' => $expectedDomain,
            ],
        ];
    }

    /**
     * @dataProvider configDoesNotCallCustomerLoginProvider
     */
    public function testCustomerLoginIsNotCalledIfConfigDoesNotAllowIt(
        ?string $customerLoginUrl,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        $_ENV['CUSTOMER_LOGIN_URL'] = $customerLoginUrl;

        $this->clientMock->expects($this->never())->method('get');

        $config = Utils::getExportConfiguration(
            $shopkey,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }

    public function configCallsCustomerLoginProvider(): array
    {
        return [
            'config has customerLoginUri and shopkey set' => [
                'customerLoginUrl' => 'https://customer-login.com',
                'shopkey' => self::VALID_SHOPKEY,
                'expectedDomain' => 'blubbergurken.io',
            ],
        ];
    }

    /**
     * @dataProvider configCallsCustomerLoginProvider
     */
    public function testCustomerLoginIsCalledIfConfigDoesAllowsItAndFailsWhenResponseIsInvalid(
        ?string $customerLoginUrl,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while tying to fetch the importer data');

        $_ENV['CUSTOMER_LOGIN_URL'] = $customerLoginUrl;

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn($this->getMockResponse('CustomerLoginResponse/invalid_response.json'));

        $config = Utils::getExportConfiguration(
            $shopkey,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }

    /**
     * @dataProvider configCallsCustomerLoginProvider
     */
    public function testCustomerLoginIsCalledIfConfigDoesAllowsIt(
        ?string $customerLoginUrl,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        $_ENV['CUSTOMER_LOGIN_URL'] = $customerLoginUrl;

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn($this->getMockResponse('CustomerLoginResponse/response.json'));

        $config = Utils::getExportConfiguration(
            $shopkey,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }
}
