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

class EngineBlock_Attributes_Definition_Denormalizer
{
    public function denormalize(array $definitions)
    {
        foreach ($definitions as $attributeName => $definition) {
            // Has a definition, no need to denormalize.
            if (is_array($definition)) {
                continue;
            }

            // Resolve aliases and remember until we get to the actual definition.
            $aliases = array($attributeName);
            while (!is_array($definition)) {
                $attributeName = $definitions[$attributeName];

                if (!isset($definitions[$attributeName])) {
                    throw new EngineBlock_Exception(
                        sprintf(
                            'Unable to resolve definition for "%s", path: "%s"',
                            $attributeName,
                            join(' > ', $aliases)
                        )
                    );
                }

                $definition = $definitions[$attributeName];
                $aliases[] = $attributeName;
            }

            // Replace the alias by the definition and add a key to show where the definition came from.
            foreach ($aliases as $alias) {
                $definitions[$alias] = $definition;
                if ($attributeName !== $alias && is_array($definitions[$alias])) {
                    $definitions[$alias]['__original__'] = $attributeName;
                }
            }
        }
        return $definitions;
    }
}
