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

/**
 * @todo Refactor this class away...
 */
class EngineBlock_Corto_CoreProxy extends EngineBlock_Corto_ProxyServer
{
    protected $_serviceToControllerMapping = array(
        'SingleSignOnService'       => 'authentication/idp/single-sign-on',
        'ContinueToIdP'             => 'authentication/idp/process-wayf',
        'AssertionConsumerService'  => 'authentication/sp/consume-assertion',
        'ContinueToSP'              => 'authentication/sp/process-consent',
        'IdpMetadataService'        => 'authentication/idp/metadata',
        'SpMetadataService'         => 'authentication/sp/metadata',
        'ProvideConsentService'     => 'authentication/idp/provide-consent',
        'ProcessConsentService'     => 'authentication/idp/process-consent',
        'ProcessedAssertionConsumerService' => 'authentication/proxy/processed-assertion'
    );

    public function getParametersFromUrl($url)
    {
        $parameters = array(
            'EntityCode'        => 'main',
            'ServiceName'       => '',
            'RemoteIdPMd5Hash'  => '',
        );
        $urlPath = parse_url($url, PHP_URL_PATH); // /authentication/x/ServiceName[/remoteIdPMd5Hash]
        if ($urlPath[0] === '/') {
            $urlPath = substr($urlPath, 1);
        }

        foreach ($this->_serviceToControllerMapping as $serviceName => $controllerUri) {
            if (strstr($urlPath, $controllerUri)) {
                $urlPath = str_replace($controllerUri, $serviceName, $urlPath);
                $urlParts = explode('/', $urlPath);
                $parameters['ServiceName'] = array_shift($urlParts);
                if (isset($urlParts[0])) {
                    $parameters['RemoteIdPMd5Hash'] = array_shift($urlParts);
                }
                return $parameters;
            }
        }

        throw new EngineBlock_Corto_ProxyServer_Exception("Unable to map URL '$url' to EngineBlock URL");
    }
    
    public function getHostedEntityUrl($entityCode, $serviceName = "", $remoteEntityId = "", $request = "")
    {
        if (!isset($this->_serviceToControllerMapping[$serviceName])) {
            return parent::getHostedEntityUrl($entityCode, $serviceName, $remoteEntityId);
        }

        $scheme = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];

        $mappedUri = $this->_serviceToControllerMapping[$serviceName];

        $isImplicitVo = false;
        $remoteEntity = false;
        if ($remoteEntityId) {
            $remoteEntity = $this->getRemoteEntity($remoteEntityId);
        }
        if ($remoteEntity && isset($remoteEntity['VoContext'])) {
            if ($request && !isset($request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX])) {
                $isImplicitVo = true;
            }
        }
        if (!$this->_processingMode && $this->_voContext !== null && $serviceName != "sPMetadataService" && !$isImplicitVo) {
            $mappedUri .= '/' . "vo:" . $this->_voContext;
        }
        if (!$this->_processingMode && $serviceName !== 'idPMetadataService' && $remoteEntityId) {
            $mappedUri .= '/' . md5($remoteEntityId);
        }
                    
        return $scheme . '://' . $host . ($this->_hostedPath ? $this->_hostedPath : '') . $mappedUri;
    }
}