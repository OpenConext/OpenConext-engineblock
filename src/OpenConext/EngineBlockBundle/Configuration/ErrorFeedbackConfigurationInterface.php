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

namespace OpenConext\EngineBlockBundle\Configuration;

interface ErrorFeedbackConfigurationInterface
{
    /**
     * @param string $route
     * @return bool
     */
    public function hasWikiLink($route);

    /**
     * @param string $route
     * @return WikiLink
     */
    public function getWikiLink($route);

    /**
     * @param string $route
     * @return bool
     */
    public function isIdPContactPage($route);

    /**
     * @param string $route
     * @return mixed
     */
    public function getIdpContactShortLabel($route);
}
