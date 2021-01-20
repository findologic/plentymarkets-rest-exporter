<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use Exception;
use FINDOLOGIC\Export\Helpers\DataHelper;
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
    public function testCustomerLoginIsCalledIfConfigDoesAllowsItAndFailsWhenResponseIsInvalid(
        array $rawConfig,
        ?string $shopkey,
        string $expectedDomain
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while tying to fetch the importer data');

        file_put_contents(self::CONFIG_PATH, Yaml::dump($rawConfig));

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn($this->getMockResponse('CustomerLoginResponse/invalid_response.json'));

        $config = Utils::getExportConfiguration(
            $shopkey,
            self::CONFIG_PATH,
            $this->clientMock
        );

        $this->assertSame($expectedDomain, $config->getDomain());
    }

    /**
     * @dataProvider configCallsCustomerLoginProvider
     */
    public function testCustomerLoginIsCalledIfConfigDoesAllowsIt(
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

    public function isEmptyDataProvider(): array
    {
        return [
            'non-empty string is considered not empty' => ['asdf', false],
            'non-zero number is considered not empty' => [1, false],
            'true is considered empty' => [true, true],
            'false is considered empty' => [false, true],
            'zero is considered empty' => [0, true],
            'null is considered empty' => [null, true],
            'null as string is considered empty' => ['null', true],
            'empty string is considered empty' => ['', true],
            'all whitespace string is considered empty' => ['       ', true],
            'string above character limit is considered empty' => [
                str_repeat('0', DataHelper::ATTRIBUTE_CHARACTER_LIMIT + 1),
                true
            ],
            'string exactly at character is not considered empty' => [
                str_repeat('0', DataHelper::ATTRIBUTE_CHARACTER_LIMIT),
                false
            ]
        ];
    }

    /**
     * @dataProvider isEmptyDataProvider
     */
    public function testIsEmpty($value, bool $expected): void
    {
        $this->assertSame($expected, Utils::isEmpty($value));
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
