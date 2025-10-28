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

final readonly class AuthzResponse
{
    public Msg $msg;
    public string|null $nonce;
    public array $attributes;
    public string $message;

    private function __construct(Msg $msg, array $jsonData)
    {
        $this->msg = $msg;
        $this->nonce = $jsonData['nonce'] ?? null;
        $this->message = $jsonData['message'] ?? '';

        if (isset($jsonData['attributes']) && is_array($jsonData['attributes'])) {
            $this->attributes = $jsonData['attributes'];
        } else {
            $this->attributes = [];
        }
    }

    public static function fromData(array $jsonData) : AuthzResponse
    {
        if (!isset($jsonData['msg'])) {
            throw new InvalidSbsResponseException('Key: "msg" was not found in the SBS response');
        }

        try {
            $msg = Msg::from($jsonData['msg']);
        } catch (\ValueError $e) {
            throw new InvalidSbsResponseException(sprintf('"%s" is not a valid msg', $jsonData['msg']));
        }

        if ($msg === Msg::Interrupt && !isset($jsonData['nonce'])) {
            throw new InvalidSbsResponseException('Key: "nonce" was not found in the SBS response');
        }

        if ($msg === Msg::Authorized && !isset($jsonData['attributes'])) {
            throw new InvalidSbsResponseException('Key: "attributes" was not found in the SBS response');
        }

        return new self($msg, $jsonData);
    }
}
