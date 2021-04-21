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

use HF\ApiClient\Exception\GatewayException;
use Laminas\Http\Headers;
use Psr\Http\Message\ResponseInterface as Response;

class ResponseHandler
{
    /** @var array|int[]  */
    private $allowedResponseCodes;

    /** @var callable  */
    private $success;

    /** @var callable  */
    private $statusCode;

    /** @var callable  */
    private $responseBody;

    public function __construct(callable $success, callable $statusCode, callable $responseBody, array $allowedResponseCodes = [200])
    {
        $this->success = $success;
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
        $this->allowedResponseCodes = $allowedResponseCodes;
    }

    public function handle(Response $response)
    {
        $contents = $response->getBody()->getContents();

        ($this->success)(false);
        ($this->statusCode)($response->getStatusCode());
        ($this->responseBody)($contents);

        if (! \in_array($response->getStatusCode(), $this->allowedResponseCodes)) {
            throw new \Exception('Incorrect response ' . $response->getStatusCode());
        }

        $headers = (new Headers())->addHeaders($response->getHeaders());

        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type');

            if (! $contentType->match(['application/json', 'application/problem+json'])) {
                throw GatewayException::backendRespondedWithMalformedPayload();
            }
        }

        ($this->success)(true);

        return \json_decode($contents, true);
    }
}
