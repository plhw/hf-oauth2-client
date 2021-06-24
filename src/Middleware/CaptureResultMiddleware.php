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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CaptureResultMiddleware
{
    private $statusCode;

    private $responseBody;

    public function __construct(&$statusCode, &$responseBody)
    {
        $this->statusCode = &$statusCode;
        $this->responseBody = &$responseBody;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) {
                $this->statusCode = $response->getStatusCode();
                $this->responseBody = $response->getBody()->getContents();

                $response->getBody()->rewind();

                return $response;
            });
        };
    }
}
