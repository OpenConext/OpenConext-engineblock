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

interface EngineBlock_Saml2_IdGenerator
{
    const ID_USAGE_SAML2_METADATA  = 'saml2-metadata';
    const ID_USAGE_OTHER           = 'other';
    const ID_USAGE_SAML2_RESPONSE  = 'saml2-response';
    const ID_USAGE_SAML2_REQUEST   = 'saml2-request';
    const ID_USAGE_SAML2_ASSERTION = 'saml2-assertion';

    public function generate($prefix = 'EB', $usage = self::ID_USAGE_OTHER);
}
