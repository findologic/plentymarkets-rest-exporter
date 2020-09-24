<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

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
                'rawConfig' => $this->getDefaultRawConfig([
                    'domain' => $expectedDomain,
                    'customerLoginUri' => null,
                ]),
                'shopkey' => self::VALID_SHOPKEY,
                'expectedDomain' => $expectedDomain,
            ],
            'config has customerLoginUri set, but no shopkey provided' => [
                'rawConfig' => $this->getDefaultRawConfig([
                    'domain' => $expectedDomain,
                    'customerLoginUri' => 'https://customer-login.com',
                ]),
                'shopkey' => null,
                'expectedDomain' => $expectedDomain,
            ],
        ];
    }

    /**
     * @dataProvider configDoesNotCallCustomerLoginProvider
     */
    public function testCustomerLoginIsNotCalledIfConfigDoesNotAllowIt(
        array $rawConfig,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        file_put_contents(self::CONFIG_PATH, Yaml::dump($rawConfig));

        $this->clientMock->expects($this->never())->method('get');

        $config = Utils::getExportConfiguration(
            $shopkey,
            self::CONFIG_PATH,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }

    public function configCallsCustomerLoginProvider(): array
    {
        return [
            'config has customerLoginUri and shopkey set' => [
                'rawConfig' => $this->getDefaultRawConfig([
                    'domain' => 'https://that-should-not-be-used.com',
                    'customerLoginUri' => 'https://customer-login.com',
                ]),
                'shopkey' => self::VALID_SHOPKEY,
                'expectedDomain' => 'blubbergurken.io',
            ],
        ];
    }

    /**
     * @dataProvider configCallsCustomerLoginProvider
     */
    public function testCustomerLoginIsCalledIfConfigDoesAllowIt(
        array $rawConfig,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        file_put_contents(self::CONFIG_PATH, Yaml::dump($rawConfig));

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn($this->getMockResponse('CustomerLoginResponse/response.json'));

        $config = Utils::getExportConfiguration(
            $shopkey,
            self::CONFIG_PATH,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }

    private function getDefaultRawConfig(array $overrides = []): array
    {
        $config = [
            'username' => 'Findologic',
            'password' => 'verySecure12',
            'domain' => 'findologic.plentymarkets-cloud02.com',
            'multiShopId' => 0,
            'availabilityId' => null,
            'priceId' => 1,
            'rrpId' => 2,
            'language' => 'DE',
            'debug' => false,
            'customerLoginUri' => null,
        ];

        return array_merge($config, $overrides);
    }
}
