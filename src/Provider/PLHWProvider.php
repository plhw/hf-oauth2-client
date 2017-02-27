<?php

declare(strict_types = 1);

namespace HF\ApiClient\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\ResponseInterface;

class PLHWProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        $options['scopeSeparator'] = ' ';

        parent::__construct($options, $collaborators);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (! empty($data['error'])) {
            $code  = 0;
            $error = $data['error_description'];
            throw new IdentityProviderException($error, $code, $data);
        }
    }
}
