<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests;

use Exception;
use FINDOLOGIC\Export\Helpers\DataHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Client as PlentyRestClient;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\PluginConfigurationRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\DirectoryAware;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    use ResponseHelper;
    use DirectoryAware;

    private const TEMP_PATH = '/tmp/rest-export';
    private const CONFIG_PATH = self::TEMP_PATH . '/config.yml';
    private const VALID_SHOPKEY = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    /** @var Client|MockObject */
    private $clientMock;

    public function setUp(): void
    {
        $this->createDirectories([self::TEMP_PATH]);

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

    public function testEnvBooleanStringsValuesAreCastedIntoBooleanValues(): void
    {
        $_ENV['TRUE_VALUE'] = 'true';
        $_ENV['FALSE_VALUE'] = 'false';

        $this->assertTrue(Utils::env('TRUE_VALUE'));
        $this->assertFalse(Utils::env('FALSE_VALUE'));

        // Reset test, so nothing is left from this test, when running a new test.
        unset($_ENV['TRUE_VALUE']);
        unset($_ENV['FALSE_VALUE']);
    }

    public function testSendIterableRequestFailsForNonIterableRequests(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'An iterable request must implement the interface "%s"',
            IterableRequestInterface::class
        ));

        $request = new PluginConfigurationRequest(1234, 1234);
        $client = $this->getMockBuilder(PlentyRestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        Utils::sendIterableRequest($client, $request);
    }
}
