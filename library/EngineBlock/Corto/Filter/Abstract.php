<?php

use EngineBlock_Corto_Filter_Command_CollabPersonIdModificationInterface as CollabPersonIdModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseAttributeSourcesModificationInterface as ResponseAttributeSourcesModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface as ResponseAttributesModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseModificationInterface as ResponseModificationInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

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
    abstract public function getCommands();

    /**
     * Filter the response.
     *
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator     $response
     * @param array                                             $responseAttributes
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProvider                             $serviceProvider
     * @param IdentityProvider                            $identityProvider
     * @throws EngineBlock_Exception
     * @throws Exception
     */
    public function filter(
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        array &$responseAttributes,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider
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

        $commands = $this->getCommands();

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

            if ($command instanceof ResponseModificationInterface) {
                $response = $command->getResponse();
            }
            if ($command instanceof ResponseAttributesModificationInterface) {
                $responseAttributes = $command->getResponseAttributes();
            }
            if ($command instanceof ResponseAttributeSourcesModificationInterface) {
                $_SESSION[$request->getId()]['attribute_sources'] = $command->getResponseAttributeSources();
            }
            if ($command instanceof CollabPersonIdModificationInterface) {
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
