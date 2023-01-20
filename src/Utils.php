<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use Exception;
use FINDOLOGIC\Export\Helpers\DataHelper;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\AuthorizationException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CriticalException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\PermissionException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry\EmptyResponseException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\ThrottlingException;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\IterableRequestInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\Request;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

final class Utils
{
    /**
     * Sends an iterable request. Iterable requests will basically return all data from a specific endpoint.
     * Returns an array of all responses.
     *
     * @param Client $client
     * @param Request $request
     *
     * @return ResponseInterface[]
     * @throws EmptyResponseException
     * @throws PermissionException
     * @throws CustomerException
     * @throws AuthorizationException
     * @throws ThrottlingException
     * @throws GuzzleException
     * @throws CriticalException
     *
     */
    public static function sendIterableRequest(Client $client, Request $request): array
    {
        if (!$request instanceof IterableRequestInterface) {
            throw new InvalidArgumentException(sprintf(
                'An iterable request must implement the interface "%s"',
                IterableRequestInterface::class
            ));
        }

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
        return $value == 'null' ||
            $value == null ||
            $value == '' ||
            (is_string($value) && !Utils::validateStringLength($value));
    }

    /**
     * When a shopkey is set, the account route is used to fetch the import data. Otherwise, the configuration
     * of the given configPath is used.
     *
     * @param string|null $shopkey
     * @param GuzzleClient|null $client
     * @return Config
     * @throws GuzzleException
     */
    public static function getExportConfiguration(
        ?string $shopkey,
        ?GuzzleClient $client = null
    ): Config {
        $importDataBaseUrl = Utils::env('IMPORT_DATA_URL');
        if ($shopkey && $importDataBaseUrl) {
            $importDataUrl = sprintf($importDataBaseUrl, $shopkey);
            return Utils::getImportConfiguration($importDataUrl, $client ?? new GuzzleClient());
        }

        return Config::fromEnvironment();
    }

    /**
     * Gets a value from the environment. If that environment variable is not set or does not contain
     * a value, such as "NULL", default may be returned.
     */
    public static function env(string $key, mixed $default = null)
    {
        if (!isset($_ENV[$key]) || (is_string($_ENV[$key]) && Utils::isEmpty(mb_strtolower($_ENV[$key])))) {
            return $default;
        }

        if ($_ENV[$key] === 'true') {
            return true;
        }

        if ($_ENV[$key] === 'false') {
            return false;
        }

        return $_ENV[$key];
    }

    public static function filterBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private static function getImportConfiguration(
        string $accountUri,
        GuzzleClient $client
    ): Config {
        $response = $client->get($accountUri);

        $rawData = json_decode($response->getBody()->__toString(), true);

        return Config::fromArray($rawData, true);
    }

    private static function parseIsLastPage(ResponseInterface $response): bool
    {
        return json_decode($response->getBody()->__toString(), true)['isLastPage'];
    }

    private static function validateStringLength(string $value): bool
    {
        return trim($value) !== '' && mb_strlen($value) <= DataHelper::ATTRIBUTE_CHARACTER_LIMIT;
    }
}
