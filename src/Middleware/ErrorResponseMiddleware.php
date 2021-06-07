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

use Fig\Http\Message\StatusCodeInterface;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Stream\JsonStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorResponseMiddleware
{
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options = []) use ($handler) {
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) {
                if ($this->isSuccessful($response)) {
                    return $response;
                }

                $this->handleErrorResponse($response);

                return $response;
            });
        };
    }

    public function isSuccessful(ResponseInterface $response)
    {
        return $response->getStatusCode() < StatusCodeInterface::STATUS_BAD_REQUEST;
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        $stream = $response->getBody();
        $content = ($stream instanceof JsonStream) ? $stream->jsonSerialize() : [];

        $title = $content['title'] ?? null;
        $detail = $content['detail'] ?? null;
        $stack = $content['stack'] ?? null;

        switch ($response->getStatusCode()) {
//            case StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY:
//                throw GatewayException::error("Unprocessble Entity",$detail);
//            case StatusCodeInterface::STATUS_NOT_FOUND:
//                throw GatewayException::error("Not Found",$detail);
            case StatusCodeInterface::STATUS_UNAUTHORIZED:
                throw new \Exception();
//            case StatusCodeInterface::STATUS_FORBIDDEN:
//                throw new \Exception;
//            case StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR:
//                throw GatewayException::error("Internal Server Error", $detail);
//            default:
//                throw new \Exception((string) $response->getBody());
        }
    }
}
