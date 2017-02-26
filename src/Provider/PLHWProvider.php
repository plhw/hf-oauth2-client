<?php

declare(strict_types = 1);

namespace HF\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class PLHWProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $env = 'production';

    /**
     * @var array|null
     */
    private $defaultScopes = null;

    /**
     * @var string
     */
    private $scopeSeparator = ' ';

    /**
     * @var string
     */
    private $responseError = 'error';

    /**
     * @var string
     */
    private $responseCode;

    /**
     * @var string
     */
    private $responseResourceOwnerId = 'user_id';

    public function __construct(array $options = [], array $collaborators = [], $env = 'production')
    {
        $this->env = $env;

        if (! in_array($this->env, ['production', 'testing', 'development'])) {
            throw new \InvalidArgumentException(sprintf("Not a valid environment '%s' given", $this->env));
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return sprintf('https://api%s.plhw.nl/oauth2/authorize', $this->env === 'production' ? '' : '-' . $this->env);
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return sprintf('https://api%s.plhw.nl/oauth2/token', $this->env === 'production' ? '' : '-' . $this->env);
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return sprintf('https://api%s.plhw.nl/identity/me', $this->env === 'production' ? '' : '-' . $this->env);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (! empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            $code  = $this->responseCode ? $data[$this->responseCode] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array|null
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator()
    {
        return $this->scopeSeparator;
    }
}
