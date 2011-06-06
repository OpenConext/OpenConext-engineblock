<?php
/**
 * SURFconext EngineBlock
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
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Group_Provider_Filter_ModelProperty_PregReplace implements EngineBlock_Group_Provider_Filter_Interface
{
    protected $_options;

    public function __construct(Zend_Config $options)
    {
        $this->_options = $options;
    }

    public function filter($data)
    {
        $modelProperties = array_keys(get_object_vars($data));
        foreach ($modelProperties as $modelProperty) {
            if (isset($this->_options->property) && $modelProperty !== $this->_options->property) {
                continue;
            }

            $data->$modelProperty = preg_replace($this->_options->search, $this->_options->replace, $data->$modelProperty);
        }
        return $data;
    }
}