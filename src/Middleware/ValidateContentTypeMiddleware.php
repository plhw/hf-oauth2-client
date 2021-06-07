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

use HF\ApiClient\Exception\GatewayException;
use Laminas\Http\Headers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ValidateContentTypeMiddleware
{
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) {
                $headers = (new Headers())->addHeaders($response->getHeaders());

                if ($headers->has('Content-Type')) {
                    $contentType = $headers->get('Content-Type');

                    if (! $contentType->match(['application/json', 'application/problem+json'])) {
                        throw GatewayException::backendRespondedWithMalformedPayload();
                    }
                }

                return $response;
            });
        };
    }
}
