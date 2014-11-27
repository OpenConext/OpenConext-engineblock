<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

abstract class EngineBlock_Corto_Filter_Abstract
{
    protected $_server;

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function _getCommands();

    /**
     * Filter the response.
     *
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator     $response
     * @param array                                             $responseAttributes
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProviderEntity                             $serviceProvider
     * @param IdentityProviderEntity                            $identityProvider
     * @throws EngineBlock_Exception
     * @throws Exception
     */
    public function filter(
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        array &$responseAttributes,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProviderEntity $serviceProvider,
        IdentityProviderEntity $identityProvider
    )
    {
        /** @var SAML2_AuthnRequest $request */
        // Note that IDs are only unique per SP... we hope...
        $responseNameId = $response->getAssertion()->getNameId();

        $sessionKey = $serviceProvider->entityId . '>' . $request->getId();
        if (isset($_SESSION[$sessionKey]['collabPersonId'])) {
            $collabPersonId = $_SESSION[$sessionKey]['collabPersonId'];
        }
        else if ($response->getCollabPersonId()) {
            $collabPersonId = $response->getCollabPersonId();
        }
        else if (isset($responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.40.40.1'][0])) {
            $collabPersonId = $responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.40.40.1'][0];
        }
        else if (!empty($responseNameId['Value'])) {
            $collabPersonId = $responseNameId['Value'];
        }
        else {
            $collabPersonId = null;
        }

        $commands = $this->_getCommands();

        /** @var EngineBlock_Corto_Filter_Command_Abstract $command */
        foreach ($commands as $command) {
            // Inject everything we have into the adapter
            $command->setProxyServer($this->_server);
            $command->setIdentityProvider($identityProvider);
            $command->setServiceProvider($serviceProvider);
            $command->setRequest($request);
            $command->setResponse($response);
            $command->setResponseAttributes($responseAttributes);
            $command->setCollabPersonId($collabPersonId);

            // Execute the command
            try {
                $command->execute();
            } catch (EngineBlock_Exception $e) {
                $e->idpEntityId = $identityProvider->entityId;
                $e->spEntityId  = $serviceProvider->entityId;
                $e->userId      = $collabPersonId;
                throw $e;
            }

            if (method_exists($command, 'getResponse')) {
                $response = $command->getResponse();
            }
            if (method_exists($command, 'getResponseAttributes')) {
                $responseAttributes = $command->getResponseAttributes();
            }
            if (method_exists($command, 'getCollabPersonId')) {
                $collabPersonId = $command->getCollabPersonId();
            }

            // Give the command a chance to stop filtering
            if (!$command->mustContinueFiltering()) {
                break;
            }
        }

        $_SESSION[$sessionKey]['collabPersonId'] = $collabPersonId;
    }
}
