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

use OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException;
use OpenConext\EngineBlockBundle\Pdp\Dto\Attribute;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AssociatedAdvice;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AttributeAssignment;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Category;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Obligation;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdentifier;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicySetIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Status;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\StatusCode;

final class EntitlementsResponse
{
    /**
     * @var Status
     */
    public $status;

    public static function fromData(array $jsonData) : EntitlementsResponse
    {
        if (!isset($jsonData['Response'])) {
            throw new InvalidPdpResponseException('Key: Response was not found in the PDP response');
        }


        $response = new self;

        $response->status = 'status';

        return $response;
    }
}
