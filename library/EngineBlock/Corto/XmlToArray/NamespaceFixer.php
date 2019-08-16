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

class EngineBlock_Corto_XmlToArray_NamespaceFixer
{
    protected $registeredNamespaces;

    protected $availableNamespaces;

    public function __construct(array $registeredNamespaces)
    {
        $this->registeredNamespaces = $registeredNamespaces;
    }

    public function fix(array $hash, $tagName = '')
    {
        // If no tagname was provided, try to get the tagname from the elements __t
        if (empty($tagName)) {
            if (!isset($hash[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX])) {
                throw new EngineBlock_Corto_XmlToArray_Exception(
                    'Unable to register namespaces, hash has no tag?' . var_export($hash, true)
                );
            }

            $tagName = $hash[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX];
        }

        $this->availableNamespaces = $this->registeredNamespaces;

        $foundNamespaces = $this->collectNamespaces($hash, $tagName);
        $hash = $this->addFoundNamespaces($foundNamespaces, $hash);

        return $hash;
    }

    protected function collectNamespaces($hash, $tagName, &$found = array())
    {
        // Detect namespaces to be registered in tagname.
        $found = static::collectNamespace($found, $tagName);

        // Detect namespaces to be registered in tag contents
        foreach ($hash as $key => $value) {
            if (in_array($key, array(EngineBlock_Corto_XmlToArray::PRIVATE_PFX, EngineBlock_Corto_XmlToArray::COMMENT_PFX), true)) {
                continue;
            }

            // Detect them in attribute names
            if (!is_array($value)) {
                $found = static::collectNamespace($found, $key);
                continue;
            }

            // And in sub tags.
            foreach ($value as $index => $subValue) {
                if (!is_array($subValue)) {
                    $found = static::collectNamespace($found, $index);
                    continue;
                }

                if (is_int($index)) {
                    $found = static::collectNamespaces($subValue, $key, $found);
                }
                else {
                    $found = static::collectNamespaces($subValue, $index, $found);
                }
            }
        }
        return $found;
    }

    protected function collectNamespace(array $found, $key)
    {
        foreach ($this->availableNamespaces as $namespaceUri => $namespaceShortName) {
            // Is the tag or attribute prefixed with this known namespace prefix
            if (strpos($key, $namespaceShortName . ':') !== 0 && strpos($key, '_' . $namespaceShortName . ':') !== 0) {
                // If not continue
                continue;
            }

            $found[$namespaceUri] = $this->availableNamespaces[$namespaceUri];
            unset($this->availableNamespaces[$namespaceUri]);

            return $found;
        }
        return $found;
    }

    protected function addFoundNamespaces($foundNamespaces, $hash)
    {
        $hash = array_reverse($hash, true);
        foreach ($foundNamespaces as $namespaceUri => $namespaceName) {
            if (isset($hash['_xmlns:' . $namespaceName])) {
                continue;
            }

            $hash['_xmlns:' . $namespaceName] = $namespaceUri;
        }
        return array_reverse($hash, true);
    }
}
