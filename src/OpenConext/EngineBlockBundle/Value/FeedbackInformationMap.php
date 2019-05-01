<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Value;

use OpenConext\EngineBlockBundle\Exception\RuntimeException;

class FeedbackInformationMap
{
    /**
     * @var array
     */
    private $data = [];

    public function add(FeedbackInformation $feedbackInformation)
    {
        $key = $feedbackInformation->getKey();
        if (array_key_exists($key, $this->data)) {
            throw new RuntimeException(
                sprintf('Feedback information with key "%s" is already mapped.', $key)
            );
        }
        $this->data[$key] = $feedbackInformation;
    }

    public function sort()
    {
        // Sort the feedback info on key
        ksort($this->data);
    }

    public function getData()
    {
        return $this->data;
    }
}
