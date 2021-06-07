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

namespace HF\ApiClient\Query;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use Laminas\Http\Header\AcceptLanguage;
use Laminas\Http\Header\UserAgent;
use PackageVersions\Versions;

class Query
{
    private $resource = '';
    private $method = 'GET';
    private $filter = [];
    private $page = [];
    private $include = [];
    private $sort = [];
    private $other = [];

    private $headers = [
        'Accept' => 'application/json',
    ];

    private $payload;

    public static $language = 'nl';

    private function __construct()
    {
    }

    public static function create(): Query
    {
        return new static();
    }

    public function withParam(string $property, $value): Query
    {
        if (! \is_scalar($value) && ! \is_array($value) && ! \is_null($value)) {
            throw new InvalidArgumentException('Value must be scalar, array or null', 0);
        }

        $query = clone $this;

        $query->other[$property] = $value;

        return $query;
    }

    public function withPayload($payload): Query
    {
        if (! \is_array($payload)) {
            throw new \UnexpectedValueException('Value must be array');
        }

        $query = clone $this;

        $query->payload = $payload;

        return $query;
    }

    public function withoutParam(string $property): Query
    {
        $query = clone $this;

        if (isset($query->other[$property])) {
            unset($query->other[$property]);
        }

        return $query;
    }

    public function param(string $property)
    {
        return $this->other[$property] ?? null;
    }

    public function withMethod(string $method): Query
    {
        $query = clone $this;

        $query->method = $method;

        return $query;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function withResource(string $resource): Query
    {
        $query = clone $this;

        $query->resource = $resource;

        return $query;
    }

    public function payload(): ?array
    {
        return $this->payload;
    }

    public function withFilter(string $property, $value): Query
    {
        if (! \is_scalar($value) && ! \is_array($value)) {
            throw new \UnexpectedValueException('Value must be scalar or array');
        }

        $query = clone $this;

        $query->filter[$property] = $value;

        return $query;
    }

    public function withoutFilter(string $property): Query
    {
        $query = clone $this;

        if (isset($query->filter[$property])) {
            unset($query->filter[$property]);
        }

        return $query;
    }

    public function withSort(string $property, bool $ascending = true): Query
    {
        $query = clone $this;

        $query->sort[] = [
            'property' => $property,
            'asc' => $ascending,
        ];

        return $query;
    }

    public function withPage(int $page = 1, int $length = 15): Query
    {
        Assertion::min($page, 1);
        Assertion::min($length, 1);

        $query = clone $this;

        $query->page['offset'] = ($page - 1) * $length;
        $query->page['limit'] = $length;

        return $query;
    }

    public function withIncluded(string $includeName): Query
    {
        if (\in_array($includeName, $this->include, true)) {
            return $this;
        }

        $query = clone $this;

        $query->include[] = $includeName;

        return $query;
    }

    public function url(): string
    {
        $query = $this->toQueryParams();

        $queryString = \http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $this->resource . ($queryString ? '?' . $queryString : '');
    }

    public function toQueryParams(): array
    {
        $query = [];

        if (! empty($this->other)) {
            foreach ($this->other as $key => $value) {
                $query[$key] = $value;
            }
        }

        if (! empty($this->filter)) {
            $query['filter'] = $this->filter;
        }
        if (! empty($this->sort)) {
            $query['sort'] = \implode(',', \array_map(function (array $sort) {
                return (! $sort['asc'] ? '-' : '') . $sort['property'];
            }, $this->sort));
        }
        if (! empty($this->page)) {
            $query['page'] = $this->page;
        }

        if (! empty($this->include)) {
            $query['include'] = \implode(',', $this->include);
        }

        return $query;
    }

    public function __toString(): string
    {
        return $this->url();
    }

    public function headers(): array
    {
        $al = new AcceptLanguage();
        $languages = ['nl', 'en'];

        if (\in_array(self::$language, $languages, true)) {
            // move to front to priorize
            $pos = \array_search(self::$language, $languages);

            \array_splice($languages, $pos, 1);
        }

        // simple prepend
        \array_unshift($languages, self::$language);

        foreach ($languages as $key => $language) {
            $al->addLanguage($language, 1 - (($key + .1) / \count($languages)));
        }

        $ua = new UserAgent(\sprintf('PLHW Api Client \'%s\'', Versions::getVersion('plhw/hf-api-client')));

        $this->headers[$ua->getFieldName()] = $ua->getFieldValue();
        $this->headers[$al->getFieldName()] = $al->getFieldValue();

        return $this->headers;
    }
}
