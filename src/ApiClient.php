<?php

declare(strict_types=1);

/*
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2017 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 */

namespace HF\ApiClient;

use HF\ApiClient\Options\Options;
use HF\ApiClient\Provider\PLHWProvider;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\Http\Headers;
use ZendService\Api\Api;

/**
 * Class ApiClient.
 *
 * @method array commerce_getArticleGroup(string $articleGroupId)
 * @method array commerce_listArticleGroups(Query $query)
 * @method array commerce_listStores(Query $query)
 * @method array customer_posAroundCoordinate(Query $query);
 * @method array dossier_attachmentsOfDossier(Query $query, string $dossierId);
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
     * @var AccessToken
     */
    private $accessToken;

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
            $this->api->setHeaders(['Authorization' => sprintf('Bearer %s', $accessToken->getToken())]);
        }

        $result  = call_user_func_array([$this->api, $name], $params);
        $headers = (new Headers())->addHeaders($this->api->getResponseHeaders());

        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type');

            if (! $contentType->match(['application/json', 'application/problem+json'])) {
                throw new \Exception(sprintf("Unexpected Content-Type of '%s' received",
                    $contentType->getFieldValue()));
            }
        }

        if (! $this->api->isSuccess()) {
            $result = $this->api->getErrorMsg();
            $result = json_decode($result, true);
        }

        if (! $this->api->isSuccess()) {
            if ($result['error'] === 'invalid_token') {
                $this->invalidateAccessToken($this->options->getGrantType(), $this->options->getScope());

                // call again
                call_user_func_array([$this, $name], $params);
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

    private function getAccessToken(
        string $grant,
        string $scope
    ): ?AccessToken {
        if ($this->accessToken === null || $this->accessToken->hasExpired()) {
            $provider = $this->createOAuth2Provider();

            $cache    = $this->getCacheStorage();
            $cacheKey = sha1($grant . serialize($scope));

            // try to get a token from the cache
            $accessToken = $cache->getItem($cacheKey, $success);

            if ($accessToken === null || ! $success || $accessToken->hasExpired()) {
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

        $cache    = $this->getCacheStorage();
        $cacheKey = sha1($grant . serialize($scope));

        $cache->removeItem($cacheKey);
    }

    private function createOAuth2Provider(): PLHWProvider
    {
        if ($this->provider === null) {
            $this->provider = new PLHWProvider([
                'clientId'                => $this->options->getClientId(),
                'clientSecret'            => $this->options->getClientSecret(),
                'redirectUri'             => $this->options->getRedirectUri(),
                'urlAuthorize'            => $this->options->getAuthorizeUri(),
                'urlAccessToken'          => $this->options->getTokenUri(),
                'urlResourceOwnerDetails' => $this->options->getResourceOwnerDetailsUri(),
            ]);
        }

        return $this->provider;
    }

    private function getCacheStorage(): StorageInterface
    {
        if ($this->cache === null) {
            $this->cache = StorageFactory::factory([
                'adapter' => [
                    'name'      => 'filesystem',
                    'dir_level' => 0,
                ],
                'plugins' => ['serializer'],
            ]);
        }

        return $this->cache;
    }
}
