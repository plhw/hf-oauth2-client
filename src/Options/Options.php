<?php

declare(strict_types=1);

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      https://plhw.nl/
 * @copyright Copyright (c) 2010 - 2017 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmnail.com>
 * @license   Proprietary License
 */

namespace HF\ApiClient\Options;

final class Options
{
    private $serverUri;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $authorizeUri;
    private $tokenUri;
    private $resourceOwnerDetailsUri;
    private $grantType;

    private function __construct(
        string $serverUri,
        string $clientId,
        string $clientSecret,
        ?string $redirectUri,
        string $authorizeUri,
        string $tokenUri,
        string $resourceOwnerDetailsUri,
        string $scope,
        string $grantType
    ) {
        $this->serverUri               = $serverUri;
        $this->clientId                = $clientId;
        $this->clientSecret            = $clientSecret;
        $this->redirectUri             = $redirectUri;
        $this->authorizeUri            = $authorizeUri;
        $this->tokenUri                = $tokenUri;
        $this->resourceOwnerDetailsUri = $resourceOwnerDetailsUri;
        $this->scope                   = $scope;
        $this->grantType               = $grantType;
    }

    public static function fromArray(array $options = []): self
    {
        return new self(
            $options['server_uri'] ?? 'https://api.plhw.nl',
            $options['client_id'] ?? 'demoapp',
            $options['client_secret'] ?? 'demoapp',
            $options['redirect_uri'] ?? null,
            $options['authorize_uri'] ?? '%s/oauth2/authorize',
            $options['token_uri'] ?? '%s/oauth2/token',
            $options['resource_owner_details_uri'] ?? '%s/identity/me',
            $options['scope'] ?? '',
            $options['grant_type'] ?? 'client_credentials'
        );
    }

    public function getServerUri(): string
    {
        return $this->serverUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function getAuthorizeUri(): string
    {
        return sprintf($this->authorizeUri, $this->serverUri);
    }

    public function getTokenUri(): string
    {
        return sprintf($this->tokenUri, $this->serverUri);
    }

    public function getResourceOwnerDetailsUri(): string
    {
        return sprintf($this->resourceOwnerDetailsUri, $this->serverUri);
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }
}
