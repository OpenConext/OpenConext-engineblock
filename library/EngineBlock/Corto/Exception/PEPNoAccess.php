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

use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;

class EngineBlock_Corto_Exception_PEPNoAccess extends EngineBlock_Exception
{
    /**
     * @var PolicyDecision
     */
    private $policyDecision;

    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

    public static function basedOn($policyDecision)
    {
        $exception = new self('Access denied after policy enforcement');
        $exception->policyDecision = $policyDecision;

        return $exception;
    }

    /**
     * @return PolicyDecision
     */
    public function getPolicyDecision()
    {
        return $this->policyDecision;
    }
}
