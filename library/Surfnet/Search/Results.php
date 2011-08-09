<?php
/**
 * SURFconext Manage
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Manage
 * @package
 * @copyright Copyright © 2010-2011 SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class Surfnet_Search_Results
{
    protected $_parameters;

    protected $_results;

    protected $_totalCount;

    /**
     * @param Surfnet_Search_Parameters $parameters
     * @param mixed $results
     * @param int $totalCount
     */
    public function __construct(Surfnet_Search_Parameters $parameters, $results, $totalCount)
    {
        $this->_parameters  = $parameters;
        $this->_results     = $results;
        $this->_totalCount  = $totalCount;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function getResultCount()
    {
        return count($this->_results);
    }

    public function getTotalCount()
    {
        return $this->_totalCount;
    }

    public function getParameters()
    {
        return $this->_parameters;
    }
}