<?php

use Twig\Environment;

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

abstract class EngineBlock_Corto_Module_Service_Abstract
    implements EngineBlock_Corto_Module_Service_Interface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var Environment
     */
    protected $twig;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Environment $twig
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->twig = $twig;
    }
}
