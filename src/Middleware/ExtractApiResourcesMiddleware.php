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

use HF\ApiClient\Stream\JsonStream;
use Laminas\Stdlib\ArrayUtils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExtractApiResourcesMiddleware
{
    private $resources;

    public function __construct(array &$resources)
    {
        $this->resources = &$resources;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ('GET' === $request->getMethod()) {
                $flatten = true;
            } else {
                $flatten = false;
            }
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($flatten) {
                if (! $flatten) {
                    return $response;
                }

                $jsonStream = new JsonStream($response->getBody());

                if ($data = $jsonStream->jsonSerialize()) {
                    $this->extractResources($data);
                }

                $jsonStream->rewind();

                return $response->withBody($jsonStream);
            });
        };
    }

    private function extractResources($result)
    {
        $cachedResources = [];

        if (isset($result['data'])) {
            if (isset($result['data']['id'])) {
                $resources = [$result['data']];
            } else {
                $resources = $result['data'];
            }

            foreach ($resources as $resource) {
                $cachedResource = $this->resources[$resource['type']][$resource['id']] ?? [];
                $cachedResource = ArrayUtils::merge($cachedResource, $resource);
                $this->resources[$resource['type']][$resource['id']] = $cachedResource;
            }
        }

        if (isset($result['included'])) {
            foreach ($result['included'] as $resource) {
                $cachedResource = $this->resources[$resource['type']][$resource['id']] ?? [];
                $cachedResource = ArrayUtils::merge($cachedResource, $resource);
                $this->resources[$resource['type']][$resource['id']] = $cachedResource;
            }
        }

        return $result;
    }
}
