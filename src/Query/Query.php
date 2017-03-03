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
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 */

namespace HF\ApiClient\Query;

use Assert\Assertion;

class Query
{
    private $filter  = [];
    private $page    = [];
    private $include = [];
    private $sort    = [];

    private function __construct()
    {
    }

    public static function create(): Query
    {
        return new static();
    }

    public function withFilter(string $property, $value = null): Query
    {
        Assertion::scalar($value);

        $query = clone $this;

        if ($value === null) {
            unset($query->filter[$term]);
        } else {
            $query->filter[$term] = $value;
        }

        return $query;
    }

    public function withSort(string $property, bool $ascending = true): Query
    {
        $query = clone $this;

        $query->sort[] = [
            'property' => $property,
            'asc'      => $ascending,
        ];

        return $query;
    }

    public function withPage(int $page = 1, int $length = 15): Query
    {
        Assertion::min($page, 1);
        Assertion::min($length, 1);

        $query = clone $this;

        $query->page['offset'] = ($page - 1) * $length;
        $query->page['limit']  = $length;

        return $query;
    }

    public function withIncluded(string $includeName): Query
    {
        if (in_array($includeName, $this->include, true)) {
            return $this;
        }

        $query = clone $this;

        $query->include[] = $includeName;

        return $query;
    }

    private function constructQuery(): string
    {
        $query = [];

        if (! empty($this->filter)) {
            $query['filter'] = $this->filter;
        }
        if (! empty($this->sort)) {
            $query['sort'] = implode(',', array_map(function (array $sort) {
                return (! $sort['asc'] ? '-' : '') . $sort['property'];
            }, $this->sort));
        }
        if (! empty($this->page)) {
            $query['page'] = $this->page;
        }

        if (! empty($this->include)) {
            $query['include'] = implode(',', $this->include);
        }

        $queryString = urldecode(http_build_query($query, '', '&', PHP_QUERY_RFC3986));

        return $queryString ? '?' . $queryString : '';
    }

    public function __toString(): string
    {
        return $this->constructQuery();
    }
}
