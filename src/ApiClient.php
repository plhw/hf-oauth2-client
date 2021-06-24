<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2021 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 *
 * @package   plhw/hf-api-client
 */

declare(strict_types=1);

namespace HF\ApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use HF\ApiClient\Middleware\AccessTokenMiddleware;
use HF\ApiClient\Middleware\CaptureResultMiddleware;
use HF\ApiClient\Middleware\ErrorResponseMiddleware;
use HF\ApiClient\Middleware\ExtractApiResourcesMiddleware;
use HF\ApiClient\Options\Options;
use HF\ApiClient\Query\Query;
use HF\ApiClient\Stream\JsonStream;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class ApiClient.
 *
 * @method array commerce_getArticleGroupOfStore(?Query $query)
 * @method array commerce_getProductGroupOfCatalogue(?Query $query)
 * @method array commerce_getProductOfProductGroup(?Query $query)
 * @method array commerce_getStore(Query $query)
 * @method array commerce_listArticleGroupsOfStore(?Query $query)
 * @method array commerce_listCataloguesOfStore(?Query $query)
 * @method array commerce_listProductGroupsOfCatalogue(?Query $query)
 * @method array commerce_listProductsOfProductGroup(?Query $query)
 * @method array commerce_listStores(?Query $query)
 * @method array commerce_retrieveSandalinosCompositionByCode(Query $query)
 * @method array commerce_submitSandalinosComposition(Query $query)
 * @method array customer_getCustomer($customerId);
 * @method array customer_getPractice($practiceId);
 * @method array customer_listPosAroundCoordinate(Query $query);
 * @method array customer_queryCustomers(Query $query);
 * @method array customer_queryPractices(Query $query);
 * @method array dossier_getAttachmentsOfDossier(?Query $query);
 * @method array dossier_getDossier(?Query $query);
 * @method array dossier_getOrder(?Query $query);
 * @method array dossier_queryDossiers(?Query $query);
 * @method array dossier_queryOrders(?Query $query);
 */
final class ApiClient
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $responseBody;

    /**
     * @var Client
     */
    private $http;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * Contains a nested array with the attributes of each loaded resource.
     *
     * example:
     *
     * $api->cachedResources = [
     *     'commerce/product' => [
     *         '4a26bcb4-4a8d-5bdf-aa91-7d89599f886c' => [
     *             "code"        => "CheyenneM38/40",
     *             "description" => "Schacht Cheyenne M38/40",
     *         ],
     *         '4c5a6a46-f3eb-5085-b3fb-f1d8439750d2' => [
     *             "code"        => "YassinM45/48",
     *             "description" => "Schacht Yassin M45/48",
     *         ],
     *     ],
     *     'commerce/product-attribute-value' => [
     *         'e906af07-a1dd-5429-aab5-28598642b645' => [
     *             "value"        => "Yassin",
     *         ],
     *         'b365d65f-b2e6-503d-9c11-3e9e9a391f0d' => [
     *             "value"        => "F",
     *         ],
     *     ],
     * ];
     *
     * @var array
     */
    public $cachedResources = [];

    /**
     * @var AccessTokenMiddleware
     */
    private $accessToken;

    private function __construct(ClientInterface $client, StorageInterface $cache, Options $options, $stack)
    {
        $this->http = $client;
        $this->options = $options;
        $this->cache = $cache;

        $stack->push(new CaptureResultMiddleware($this->statusCode, $this->responseBody));
        $stack->push(new ExtractApiResourcesMiddleware($this->cachedResources));

        $this->accessToken = new AccessTokenMiddleware($options, $cache);
        $stack->push($this->accessToken);
    }

    public static function createClient(Options $options, ?StorageInterface $cache = null): self
    {
        if (! $cache) {
            // optional but will then use filesystem default tmp directory
            $cache = StorageFactory::factory([
                'adapter' => [
                    'name' => 'memory',
                ],
            ]);
        }

        $stack = HandlerStack::create();
        $stack->push(new ErrorResponseMiddleware());

        $client = new Client(['base_uri' => $options->getServerUri(), 'handler' => $stack]);

        $stack->push(Middleware::mapResponse(function (Response $response) {
            $jsonStream = new JsonStream($response->getBody());

            return $response->withBody($jsonStream);
        }));

        return new static($client, $cache, $options, $stack);
    }

    /**
     * Proxies calls to API instance.
     *
     * @param $name
     * @param $params
     *
     * @return mixed
     *
     * @throws IdentityProviderException
     */
    public function __call($name, $params)
    {
        $this->statusCode = null;
        $this->lastResponseBody = null;

        $path = __DIR__ . '/../data/v1/' . $name . '.php';

        if (! \file_exists($path)) {
            throw new \Exception(\sprintf('\'%s\' does not exist', $name));
        }

        /** $query Query */
        [$query] = $params;

        // modifies the query

        /** $query Query */
        $query = include $path;

        return $this->request($query);
    }

    private function request(Query $query)
    {
        try {
            /* @var \GuzzleHttp\Psr7\Response $response */
            $response = $this->http->request($query->method(), $query->url(), $this->buildOptionsForRequest($query));

            return $response->getBody()->jsonSerialize();
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            switch ($clientException->getCode()) {
                case 401:
                    // invalidate token and try again
                    $this->accessToken->invalidate($this->options->getGrantType(), $this->options->getScope());

                    \call_user_func_array([$this, 'request'], $query);

                    break;
                default:
                    throw $clientException;
            }
        }
    }

    private function buildOptionsForRequest(Query $query)
    {
        $options = [];

        $options['headers'] = $query->headers();

        if ($query->payload()) {
            $key = 'GET' === $query->method() ? 'query' : 'json';
            $options[$key] = $query->payload();
        }

        return $options;
    }

    public function isSuccess(): bool
    {
        return \in_array($this->statusCode, [200, 202], true);
    }

    public function getStatusCode(): int
    {
        return (int) $this->statusCode;
    }

    public function getLastResponseBody(): ?string
    {
        return $this->responseBody;
    }

    private function invalidateAccessToken(string $grant, string $scope): void
    {
        $cache = $this->getCacheStorage();
        $cacheKey = \sha1($grant . \serialize($scope));

        $cache->removeItem($cacheKey);
    }

    private function getCacheStorage(): StorageInterface
    {
        if (null === $this->cache) {
            $this->cache = StorageFactory::factory([
                'adapter' => [
                    'name' => 'filesystem',
                    'dir_level' => 0,
                ],
                'plugins' => ['serializer'],
            ]);
        }

        return $this->cache;
    }
}
