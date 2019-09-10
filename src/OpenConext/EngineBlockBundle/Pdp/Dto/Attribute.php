<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Pdp\Dto;

use JsonSerializable;

final class Attribute implements JsonSerializable
{
    /**
     * @var string
     */
    public $attributeId;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $dataType;

    public function jsonSerialize()
    {
        $attribute = [
            'AttributeId' => $this->attributeId,
            'Value'       => $this->value,
        ];

        if ($this->dataType !== null) {
            $attribute['DataType'] = $this->dataType;
        }

        return $attribute;
    }
}
