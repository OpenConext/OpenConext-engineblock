<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

/**
 * Representation of an IdP list. This model is used in the Wayf template to load and filter
 * IdP data.
 */
class ConnectedIdps
{
    private $formattedPreviousSelectionList = [];

    private $formattedIdpList = [];

    public function __construct(array $formattedPreviousSelectionList, array $formattedIdpList)
    {
        $this->formattedPreviousSelectionList = $formattedPreviousSelectionList;
        $this->formattedIdpList = $formattedIdpList;
    }

    /**
     * Merges the Previously selected IdP's and the currently available IdP's into one single array.
     *
     * @return array
     */
    public function getMergedIdpList()
    {
        return array_merge($this->formattedPreviousSelectionList, $this->getConnectedIdps());
    }

    /**
     * Filters out all connected IdP's from the formattedIdpList.
     * @return array
     */
    public function getConnectedIdps()
    {
        return array_filter(
            $this->formattedIdpList,
            function ($idp) {
                return $idp['connected'];
            }
        );
    }

    public function getFormattedIdpList()
    {
        return $this->formattedIdpList;
    }

    public function getFormattedPreviousSelectionList()
    {
        return $this->formattedPreviousSelectionList;
    }
}
