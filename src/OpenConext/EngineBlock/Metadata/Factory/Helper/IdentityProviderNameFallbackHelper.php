<?php declare(strict_types=1);
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

namespace OpenConext\EngineBlock\Metadata\Factory\Helper;

use OpenConext\EngineBlock\Metadata\Factory\Decorator\AbstractIdentityProvider;

/**
 * Represents a a helper to facilitate name fallback rules.
 */
class IdentityProviderNameFallbackHelper extends AbstractIdentityProvider
{
    /**
     * Best effort to show a fallback display name.
     *
     * Buisiness rule:
     * displayname:{locale}, name:{locale}, name:en
     *
     * @param $locale string
     * @return string
     */
    public function getDisplayName($locale): string
    {
        if (empty($this->entity->getDisplayName($locale))) {
            if (!empty($this->entity->getName($locale))) {
                return $this->entity->getName($locale);
            }
            return $this->entity->getName('en');
        }
        return $this->entity->getDisplayName($locale);
    }
}
