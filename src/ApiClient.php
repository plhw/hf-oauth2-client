<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2018 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 *
 * @package   plhw/hf-api-client
 */

declare(strict_types=1);

namespace HF\ApiClient;

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Options\Options;
use HF\ApiClient\Provider\PLHWProvider;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\Http\Headers;
use Zend\Stdlib\ArrayUtils;
use ZendService\Api\Api;

/**
 * Class ApiClient.
 *
 * @method array commerce_submitSandalinosComposition(Query $query, string $storeId)
 * @method array commerce_retrieveSandalinosCompositionByCode(Query $query, string $storeId)
 * @method array commerce_getArticleGroupOfStore(?Query $query, string $storeId, string $articleGroupId)
 * @method array commerce_getProductGroupOfCatalogue(?Query $query, string $storeId, string $catalogueId, string $productGroupId)
 * @method array commerce_getProductOfProductGroup(?Query $query, string $storeId, string $catalogueId, string $productGroupId, string $productId)
 * @method array commerce_getStore(Query $query, string $storeId)
 * @method array commerce_listArticleGroupsOfStore(?Query $query, string $storeId)
 * @method array commerce_listProductGroupsOfCatalogue(?Query $query, string $storeId, string $catalogueId)
 * @method array commerce_listProductsOfProductGroup(?Query $query, string $storeId, string $catalogueId, string $productGroupId)
 * @method array commerce_listCataloguesOfStore(?Query $query, string $storeId)
 * @method array commerce_listStores(?Query $query)
 * @method array customer_listPosAroundCoordinate(Query $query);
 * @method array dossier_getAttachmentsOfDossier(?Query $query, string $dossierId);
 */
final class ApiClient
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var
     */
    private $provider;

    /**
     * @var Api
     */
    private $api;

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
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $lastResponseBody = null;

    private function __construct(Options $options)
    {
        $this->options = $options;
    }

    public static function createClient(Options $options, StorageInterface $cache = null): self
    {
        $new = new static($options);

        $new->api = new Api();
        $new->api->setUrl($options->getServerUri());
        $new->api->setApiPath(__DIR__ . '/../data/v1');

        $new->cache = $cache;

        return $new;
    }

    /**
     * Proxies calls to API instance.
     *
     * @param $name
     * @param $params
     *
     * @throws IdentityProviderException
     *
     * @return mixed
     */
    public function __call($name, $params)
    {
        if ($accessToken = $this->getAccessToken($this->options->getGrantType(), $this->options->getScope())) {
            $this->api->setHeaders(['Authorization' => \sprintf('Bearer %s', $accessToken->getToken())]);
        }

        $result = \call_user_func_array([$this->api, $name], $params);
        $headers = (new Headers())->addHeaders($this->api->getResponseHeaders());
        $this->lastResponseBody = $this->api->getHttpClient()->getResponse()->getBody();

        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type');

            if (! $contentType->match(['application/json', 'application/problem+json'])) {
                throw GatewayException::backendRespondedWithMalformedPayload();
            }
        }

        if (! $this->api->isSuccess()) {
            $result = $this->api->getErrorMsg();
            $result = \json_decode($result, true);
        }

        if (! $this->api->isSuccess()) {
            if ('invalid_token' === @$result['error']) {
                $this->invalidateAccessToken($this->options->getGrantType(), $this->options->getScope());

                // call again
                \call_user_func_array([$this, $name], $params);
            }
        }

        if (isset($result['data'])) {
            if (isset($result['data']['id'])) {
                $resources = [$result['data']];
            } else {
                $resources = $result['data'];
            }

            foreach ($resources as $resource) {
                $cachedResource = $this->cachedResources[$resource['type']][$resource['id']] ?? [];
                $cachedResource = ArrayUtils::merge($cachedResource, $resource);
                unset($cachedResource['id'], $cachedResource['type']);
                $this->cachedResources[$resource['type']][$resource['id']] = $cachedResource;
            }
        }

        if (isset($result['included'])) {
            foreach ($result['included'] as $resource) {
                $cachedResource = $this->cachedResources[$resource['type']][$resource['id']] ?? [];
                $cachedResource = ArrayUtils::merge($cachedResource, $resource);
                unset($cachedResource['id'], $cachedResource['type']);
                $this->cachedResources[$resource['type']][$resource['id']] = $cachedResource;
            }
        }

        return $result;
    }

    public function isSuccess(): bool
    {
        return $this->api->isSuccess();
    }

    public function getStatusCode(): int
    {
        return (int) $this->api->getStatusCode();
    }

    public function getLastResponseBody(): ?string
    {
        return $this->lastResponseBody;
    }

    private function getAccessToken(
        string $grant,
        string $scope
    ): ?AccessToken {
        if (null === $this->accessToken || $this->accessToken->hasExpired()) {
            $provider = $this->createOAuth2Provider();

            $cache = $this->getCacheStorage();
            $cacheKey = \sha1($grant . \serialize($scope));

            // try to get a token from the cache
            $accessToken = $cache->getItem($cacheKey, $success);

            if (null === $accessToken || ! $success || $accessToken->hasExpired()) {
                // try to get a new access token
                $accessToken = $provider->getAccessToken($grant, [
                    'scope' => $scope,
                ]);

                $cache->setItem($cacheKey, $accessToken);
            }

            $this->accessToken = $accessToken;
        }

        return $this->accessToken;
    }

    private function invalidateAccessToken(
        string $grant,
        string $scope
    ): void {
        $this->accessToken = null;

        $cache = $this->getCacheStorage();
        $cacheKey = \sha1($grant . \serialize($scope));

        $cache->removeItem($cacheKey);
    }

    private function createOAuth2Provider(): PLHWProvider
    {
        if (null === $this->provider) {
            $this->provider = new PLHWProvider([
                'clientId' => $this->options->getClientId(),
                'clientSecret' => $this->options->getClientSecret(),
                'redirectUri' => $this->options->getRedirectUri(),
                'urlAuthorize' => $this->options->getAuthorizeUri(),
                'urlAccessToken' => $this->options->getTokenUri(),
                'urlResourceOwnerDetails' => $this->options->getResourceOwnerDetailsUri(),
            ]);
        }

        return $this->provider;
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
