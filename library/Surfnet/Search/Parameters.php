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

class Surfnet_Search_Parameters
{
    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

    protected $_limit;

    protected $_offset = 0;

    protected $_sortField;

    protected $_sortDirection = self::SORT_DIRECTION_ASC;

    protected $_searchParams = array();

    protected function __construct()
    {
    }

    /**
     * @return Surfnet_Search_Parameters
     */
    public static function create()
    {
        return new self();
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * @return Surfnet_Search_Parameters
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * @return Surfnet_Search_Parameters
     */
    public function setOffset($offset)
    {
        $this->_offset = (int)$offset;
        return $this;
    }

    public function getSortByField()
    {
        return $this->_sortField;
    }

    /**
     * @return Surfnet_Search_Parameters
     */
    public function setSortByField($sortField)
    {
        $this->_sortField = $sortField;
        return $this;
    }

    public function getSortDirection()
    {
        return $this->_sortDirection;
    }

    public function addSearchParam($name, $value)
    {
        $this->_searchParams[$name] = $value;
        return $this;
    }

    /**
     * Set the search parameters all at once
     * 
     * @var    Array
     * @return Surfnet_Search_Parameters
     */
    public function setSearchParams(Array $params)
    {
        $this->_searchParams = $params;
        return $this;
    }

    public function getSearchParams()
    {
        return $this->_searchParams;
    }

    /**
     * @return Surfnet_Search_Parameters
     */
    public function setSortDirection($direction)
    {
        if (!in_array($direction, array(self::SORT_DIRECTION_ASC, self::SORT_DIRECTION_DESC))) {
            throw new Exception("Unrecognized sort direction");
        }

        $this->_sortDirection = $direction;
        return $this;
    }
}