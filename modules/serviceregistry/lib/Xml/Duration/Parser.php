<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require 'Parser/FormatException.php';

/**
 * Recursive Descent parser (LL(1)) for XML xs:duration format.
 *
 * Parse an XML duration and return the UNIX timestamp when the duration ends.
 *
 * "The duration data type is used to specify a time interval.
 *
 * The time interval is specified in the following form "PnYnMnDTnHnMnS" where:
 *
 *  P indicates the period (required)
 *  nY indicates the number of years
 *  nM indicates the number of months
 *  nD indicates the number of days
 *  T indicates the start of a time section (required if you are going to specify hours, minutes, or seconds)
 *  nH indicates the number of hours
 *  nM indicates the number of minutes
 *  nS indicates the number of seconds"
 * @url http://www.w3schools.com/Schema/schema_dtypes_date.asp
 *
 * Note that durations are relative, for instance:
 * "P3M", meaning a period of 3 months, can have more or less days / minutes / seconds depending on
 * which 3 months are included (february has less days than july).
 */ 
class Xml_Duration_Parser
{
    /**
     * @var xs:duration format to parse
     */
    protected $_duration;

    /**
     * @var int Duration from which Unix Time
     */
    protected $_fromTime;

    /**
     * @var int Number of seconds the duration is.
     */
    protected $_seconds = 0;

    /**
     * @var int Duration in future or past (future *= 1, past *= -1)
     */
    protected $_multiplier = 1;

    /**
     * @var string Token currently being parsed
     */
    protected $_token;

    /**
     * @var int Index of token currently being parsed;
     */
    protected $_tokenIndex = 0;

    public function __construct($duration, $fromTime = null)
    {
        $this->_duration = $duration;

        if ($fromTime === null) {
            $fromTime = time();
        }
        $this->_fromTime = $fromTime;
    }

    public function getSeconds()
    {
        return $this->_seconds;
    }

    public function parse()
    {
        $this->_nextToken();
        if ($this->_token === '-') {
            $this->_multiplier = -1;
            $this->_nextToken();
        }

        if ($this->_token !== 'P') {
            throw new Xml_Duration_Parser_FormatException("Duration '{$this->_duration}' is not in the XML duration format?!?");
        }

        $this->_parseDate();

        return $this;
    }

    protected function _parseDate()
    {
        $this->_nextToken();
        if ($this->_token === 'T') {
            $this->_parseTime();
        }
        else if (is_numeric($this->_token)) {
            $number = '';
            while (is_numeric($this->_token)) {
                $number .= $this->_token;
                $this->_nextToken();
            }
            $this->_parseDateModifier($number);
            $this->_parseDate();
        }
    }

    protected function _parseDateModifier($number)
    {
        if ($this->_token === 'Y') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'years') * $this->_multiplier;
        }
        else if ($this->_token === 'M') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'months') * $this->_multiplier;
        }
        else if ($this->_token === 'D') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'days') * $this->_multiplier;
        }
        else {
            throw new Xml_Duration_Parser_FormatException("Unrecognized token '$this->_token' in '{$this->_duration}'");
        }
    }

    protected function _parseTime()
    {
        $this->_nextToken();
        if (is_numeric($this->_token)) {
            $number = '';
            while (is_numeric($this->_token)) {
                $number .= $this->_token;
                $this->_nextToken();
            }
            $this->_parseTimeModifier($number);
            $this->_parseTime();
        }
    }

    protected function _parseTimeModifier($number)
    {
        if ($this->_token === 'H') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'hours') * $this->_multiplier;
        }
        else if ($this->_token === 'M') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'minutes') * $this->_multiplier;
        }
        else if ($this->_token === 'S') {
            $this->_seconds += $this->_getSecondsForUnit($number, 'seconds') * $this->_multiplier;
        }
        else {
            throw new Xml_Duration_Parser_FormatException("Unrecognized token '$this->_token' in '{$this->_duration}'");
        }
    }

    protected function _getSecondsForUnit($number, $unit)
    {
        $strtotime = "$number $unit";
        return strtotime($strtotime, $this->_fromTime) - $this->_fromTime;
    }

    protected function _nextToken()
    {
        $this->_token = $this->_duration[$this->_tokenIndex];
        $this->_tokenIndex += 1;
        return $this->_token;
    }
}