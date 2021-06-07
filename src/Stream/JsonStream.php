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

namespace HF\ApiClient\Stream;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use HF\ApiClient\Exception\GatewayException;
use JsonSerializable;
use Psr\Http\Message\StreamInterface;

class JsonStream implements StreamInterface, JsonSerializable
{
    use StreamDecoratorTrait;

    public function jsonSerialize()
    {
        $contents = (string) $this->getContents();

        if ('' === $contents) {
            return null;
        }

        $decodedContents = \json_decode($contents, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw GatewayException::couldNotDecodeJson(\json_last_error_msg());
        }

        return $decodedContents;
    }
}
