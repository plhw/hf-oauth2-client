<?php

declare(strict_types = 1);

namespace HF\ApiClient\Provider;

use League\OAuth2\Client\Provider\GenericProvider;

class PLHWProvider extends GenericProvider
{
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
    private $accessTokenMethod;

    /**
     * @var string
     */
    private $accessTokenResourceOwnerId;

    /**
     * @var array|null
     */
    private $scopes = null;

    /**
     * @var string
     */
    private $responseResourceOwnerId = 'id';

    public function __construct(array $options = [], array $collaborators = [])
    {
        $options['scopeSeparator'] = ' ';

        parent::__construct($options, $collaborators);
    }
}
