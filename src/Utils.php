<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class Utils
{
    /**
     * Sends an iterable request. Iterable requests will basically return all data from a specific endpoint.
     * Returns an array of all responses.
     *
     * @param Client $client
     * @param IterableRequestInterface|Request $request
     * @return ResponseInterface[]
     */
    public static function sendIterableRequest(Client $client, IterableRequestInterface $request): array
    {
        $responses = [];
        $lastPage = false;
        while (!$lastPage) {
            $response = $client->send($request);
            $lastPage = self::parseIsLastPage($response);
            $request->setPage($request->getPage() + 1);

            $responses[] = $response;
        }

        return $responses;
    }

    public static function validateAndGetShopkey(?string $shopkey): ?string
    {
        if (!$shopkey) {
            return null;
        }

        if (!preg_match('/^[A-F0-9]{32}$/', $shopkey)) {
            throw new InvalidArgumentException('Given shopkey does not match the shopkey format.');
        }

        return $shopkey;
    }

    public static function isEmpty($value): bool
    {
        return $value == 'null' || $value == null || $value == '' || (is_string($value) && trim($value) === '');
    }

    /**
     * When a shopkey is set, the customer-login route is used to fetch the import data. Otherwise the configuration
     * of the given configPath is used.
     *
     * @param string|null $shopkey
     * @param GuzzleClient|null $client
     * @return Config
     */
    public static function getExportConfiguration(
        ?string $shopkey,
        ?GuzzleClient $client = null
    ): Config {
        $customerLoginUri = static::env('CUSTOMER_LOGIN_URL');
        if ($shopkey && $customerLoginUri) {
            return static::getCustomerLoginConfiguration($customerLoginUri, $shopkey, $client ?? new GuzzleClient());
        }

        return Config::fromEnvironment();
    }

    /**
     * Gets a value from the environment. If that environment variable is not set or does not contain
     * a value, such as "NULL", default may be returned.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function env(string $key, $default = null)
    {
        if (!isset($_ENV[$key]) || (is_string($_ENV[$key]) && static::isEmpty(mb_strtolower($_ENV[$key])))) {
            return $default;
        }

        return $_ENV[$key];
    }

    private static function getCustomerLoginConfiguration(
        string $customerLoginUri,
        string $shopkey,
        GuzzleClient $client
    ): Config {
        $response = $client->get($customerLoginUri, [
            RequestOptions::QUERY => ['shopkey' => $shopkey]
        ]);

        $rawData = json_decode($response->getBody()->__toString(), true);

        return Config::parseByCustomerLoginResponse($rawData, true);
    }

    private static function parseIsLastPage(ResponseInterface $response): bool
    {
        return json_decode($response->getBody()->__toString(), true)['isLastPage'];
    }
}
