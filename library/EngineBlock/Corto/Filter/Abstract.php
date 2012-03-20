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

abstract class EngineBlock_Corto_Filter_Abstract
{
    protected $_adapter;

    public function __construct(EngineBlock_Corto_Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function _getCommands();

    /**
     * Filter the response.
     *
     * @param array $response
     * @param array $responseAttributes
     * @param array $request
     * @param array $spEntityMetadata
     * @param array $idpEntityMetadata
     * @return void
     */
    public function filter(
        array &$response,
        array &$responseAttributes,
        array $request,
        array $spEntityMetadata,
        array $idpEntityMetadata
    )
    {
        $commands = $this->_getCommands();

        /** @var EngineBlock_Corto_Filter_Command_Abstract $command */
        foreach ($commands as $command) {
            // Inject everything we have into the adapter
            $command->setAdapter($this->_adapter);
            $command->setIdpMetadata($idpEntityMetadata);
            $command->setSpMetadata($spEntityMetadata);
            $command->setRequest($request);
            $command->setResponse($response);
            $command->setResponseAttributes($responseAttributes);

            // Execute the command
            $command->execute();

            if (method_exists($command, 'getResponse')) {
                $response = $command->getResponse();
            }
            if (method_exists($command, 'getResponseAttributes')) {
                $responseAttributes = $command->getResponseAttributes();
            }

            // Give the command a chance to stop filtering
            if (!$command->mustContinueFiltering()) {
                break;
            }
        }
    }
}