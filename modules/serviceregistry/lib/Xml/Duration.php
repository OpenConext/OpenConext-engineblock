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

/**
 *
 */ 
class Xml_Duration
{
    protected $_seconds = 0;

    public static function createFromUnixTime($seconds)
    {
        return new self($seconds);
    }

    public static function createFromDuration($duration)
    {
        $parser = new Xml_Duration_Parser($duration);
        $parser->parse();
        return new self($parser->getSeconds());
    }

    protected function __construct($seconds)
    {
        $this->_seconds = $seconds;
    }

    public function getSeconds()
    {
        return $this->_seconds;
    }
}
