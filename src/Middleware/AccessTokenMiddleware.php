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

namespace HF\ApiClient\Middleware;

use GuzzleHttp\Psr7\Utils;
use HF\ApiClient\Exception\ClientException;
use HF\ApiClient\Options\Options;
use HF\ApiClient\Provider\PLHWProvider;
use Laminas\Cache\Storage\StorageInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\RequestInterface;

class AccessTokenMiddleware
{
    private $cache;
    private $options;
    private $accessToken;

    public function __construct(Options $options, StorageInterface $cache)
    {
        $this->options = $options;
        $this->cache = $cache;
    }

    public function __invoke(callable $next)
    {
        return function (RequestInterface $request, array $options = []) use ($next) {
            $request = $this->applyToken($request);

            return $next($request, $options);
        };
    }

    protected function applyToken(RequestInterface $request)
    {
        try {
            if ($accessToken = $this->getAccessToken($this->options->getGrantType(), $this->options->getScope())) {
                return Utils::modifyRequest($request, [
                    'set_headers' => [
                        'Authorization' => \sprintf('Bearer %s', $accessToken->getToken()),
                    ],
                ]);
            }
        } catch (IdentityProviderException $e) {
            throw ClientException::couldNotGetValidAccessToken($e->getMessage());
        }

        return $request;
    }

    public function invalidate(string $grant, string $scope): void
    {
        $this->accessToken = null;

        $cacheKey = \sha1($grant . \serialize($scope));

        $this->cache->removeItem($cacheKey);
    }

    private function getAccessToken(string $grant, string $scope): ?AccessToken
    {
        if (null === $this->accessToken || $this->accessToken->hasExpired()) {
            $provider = $this->createOAuth2Provider();

            $cacheKey = \sha1($grant . \serialize($scope));

            // try to get a token from the cache
            $accessToken = $this->cache->getItem($cacheKey, $success);

            if (null === $accessToken || ! $success || $accessToken->hasExpired()) {
                // try to get a new access token
                $accessToken = $provider->getAccessToken($grant, [
                    'scope' => $scope,
                ]);

                $this->cache->setItem($cacheKey, $accessToken);
            }

            $this->accessToken = $accessToken;
        }

        return $this->accessToken;
    }

    private function createOAuth2Provider(): PLHWProvider
    {
        return new PLHWProvider([
            'clientId' => $this->options->getClientId(),
            'clientSecret' => $this->options->getClientSecret(),
            'redirectUri' => $this->options->getRedirectUri(),
            'urlAuthorize' => $this->options->getAuthorizeUri(),
            'urlAccessToken' => $this->options->getTokenUri(),
            'urlResourceOwnerDetails' => $this->options->getResourceOwnerDetailsUri(),
        ]);
    }
}
