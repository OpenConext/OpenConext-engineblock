<?php

/**
 * Copyright 2025 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockBundle\Sbs;

use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;

final class AuthzResponse
{
    /**
     * @var string
     */
    public $msg;

    /**
     * @var $nonce
     */
    public $nonce;

    /**
     * @var array
     */
    public $attributes;

    public static function fromData(array $jsonData) : AuthzResponse
    {
        if (!isset($jsonData['msg'])) {
            throw new InvalidSbsResponseException('Key: "msg" was not found in the SBS response');
        }

        if (!in_array($jsonData['msg'], SbsClientInterface::VALID_MESSAGES, true)) {
            throw new InvalidSbsResponseException(sprintf('Msg: "%s" is not a valid message', $jsonData['msg']));
        }

        if (($jsonData['msg'] === SbsClientInterface::INTERRUPT) && !isset($jsonData['nonce'])) {
            throw new InvalidSbsResponseException('Key: "nonce" was not found in the SBS response');
        }

        if (($jsonData['msg'] === SbsClientInterface::AUTHORIZED) && !isset($jsonData['attributes'])) {
            throw new InvalidSbsResponseException('Key: "attributes" was not found in the SBS response');
        }

        $response = new self;
        $response->msg = $jsonData['msg'];
        $response->nonce = $jsonData['nonce'] ?? null;
        $response->message = $jsonData['message'] ?? null;

        if (is_array($jsonData['attributes'])) {
            $response->attributes = $jsonData['attributes'];
        } else {
            $response->attributes = [];
        }

        return $response;
    }
}
