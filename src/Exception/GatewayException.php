<?php

declare(strict_types=1);

/*
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2017 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 */

namespace HF\ApiClient\Exception;

final class GatewayException extends \RuntimeException
{
    public static function backendRespondedWithMalformedPayload(): GatewayException
    {
        return new self('The backend responded with a malformed payload');
    }
}
