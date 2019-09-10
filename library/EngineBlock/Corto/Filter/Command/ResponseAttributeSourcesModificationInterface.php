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

interface EngineBlock_Corto_Filter_Command_ResponseAttributeSourcesModificationInterface
{
    /**
     * Get metadata about origin of response attributes.
     *
     * Attributes provided by the attribute aggregator come from a specific
     * soure which engineblock must track in order to show the source on the
     * consent page.
     *
     * The sources list has the following structure:
     *
     *    attribute name => attribute source
     *
     * @param array $sources
     */
    public function getResponseAttributeSources();
}
