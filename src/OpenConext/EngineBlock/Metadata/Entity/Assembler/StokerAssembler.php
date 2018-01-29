<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use DOMDocument;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\Component\StokerMetadata\MetadataIndex;
use RuntimeException;
use SAML2\Constants;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IDPSSODescriptor;
use SAML2\XML\md\RoleDescriptor;
use SAML2\XML\md\SPSSODescriptor;
use SAML2\XML\mdui\Logo as UILogo;
use SAML2\XML\mdui\UIInfo;

/**
 * Class StokerAssembler
 * @package OpenConext\EngineBlock\Metadata\Entity\Translator
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class StokerAssembler
{
    /**
     * @param string $entityXml
     * @param MetadataIndex\Entity $metadataIndexEntity
     * @return IdentityProvider|ServiceProvider
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assemble($entityXml, MetadataIndex\Entity $metadataIndexEntity)
    {
        $document = new DOMDocument();
        $document->loadXML($entityXml);

        $entityDescriptor = new EntityDescriptor($document->documentElement);

        $idpDescriptor = null;
        $spDescriptor = null;
        foreach ($entityDescriptor->RoleDescriptor as $role) {
            if ($role instanceof IDPSSODescriptor) {
                if ($idpDescriptor) {
                    throw new RuntimeException('More than 1 IDPSSODescriptor found');
                }
                $idpDescriptor = $role;
            }
            if ($role instanceof SPSSODescriptor) {
                if ($spDescriptor) {
                    throw new RuntimeException('More than 1 SPSSODescriptor found');
                }
                $spDescriptor = $role;
            }
        }

        if (!$idpDescriptor && !$spDescriptor) {
            throw new RuntimeException('Entity is neither IDP nor SP?');
        }
        if ($spDescriptor && $idpDescriptor) {
            // @todo warn: adding only the idp side!
            return $this->assembleIdentityProvider($metadataIndexEntity, $entityDescriptor, $idpDescriptor);
        }
        if ($spDescriptor) {
            return $this->assembleServiceProvider($metadataIndexEntity, $entityDescriptor, $spDescriptor);
        }
        if ($idpDescriptor) {
            return $this->assembleIdentityProvider($metadataIndexEntity, $entityDescriptor, $idpDescriptor);
        }

        throw new RuntimeException('Boolean logic no longer works, assume running as part of the Heart of Gold.');
    }

    /**
     * @param MetadataIndex\Entity $metadataIndexEntity
     * @param AbstractRole $entity
     * @param RoleDescriptor $role
     * @return AbstractRole
     */
    private function assembleCommon(
        MetadataIndex\Entity $metadataIndexEntity,
        AbstractRole $entity,
        RoleDescriptor $role
    ) {
        $entity->displayNameNl = $metadataIndexEntity->displayNameNl;
        $entity->displayNameEn = $metadataIndexEntity->displayNameEn;

        foreach ($role->Extensions as $extension) {
            if (!$extension instanceof UIInfo) {
                continue;
            }

            if (empty($extension->Logo)) {
                continue;
            }

            /** @var UILogo $logo */
            $logo = $extension->Logo[0];
            $entity->logo = new Logo($logo->url);
            $entity->logo->height = $logo->height;
            $entity->logo->width  = $logo->width;
        }

        return $entity;
    }

    /**
     * @param MetadataIndex\Entity $metadataIndexEntity
     * @param EntityDescriptor $entityDescriptor
     * @param IDPSSODescriptor $idpDescriptor
     * @return AbstractRole|IdentityProvider
     */
    protected function assembleIdentityProvider(
        MetadataIndex\Entity $metadataIndexEntity,
        EntityDescriptor $entityDescriptor,
        IDPSSODescriptor $idpDescriptor
    ) {
        $entity = new IdentityProvider($entityDescriptor->entityID);

        $entity = $this->assembleCommon($metadataIndexEntity, $entity, $idpDescriptor);

        $singleSignOnServices = array();
        foreach ($idpDescriptor->SingleSignOnService as $ssos) {
            if (!in_array($ssos->Binding, array(Constants::BINDING_HTTP_POST, Constants::BINDING_HTTP_REDIRECT))) {
                continue;
            }

            $singleSignOnServices[] = new Service($ssos->Location, $ssos->Binding);
        }
        $entity->singleSignOnServices = $singleSignOnServices;
        return $entity;
    }

    /**
     * @param MetadataIndex\Entity $metadataIndexEntity
     * @param EntityDescriptor $entityDescriptor
     * @param SPSSODescriptor $spDescriptor
     * @return AbstractRole
     */
    protected function assembleServiceProvider(
        MetadataIndex\Entity $metadataIndexEntity,
        EntityDescriptor $entityDescriptor,
        SPSSODescriptor $spDescriptor
    ) {
        $entity = new ServiceProvider($entityDescriptor->entityID);

        $entity = $this->assembleCommon($metadataIndexEntity, $entity, $spDescriptor);

        $assertionConsumerServices = array();
        foreach ($spDescriptor->AssertionConsumerService as $acs) {
            if (!in_array($acs->Binding, array(Constants::BINDING_HTTP_POST, Constants::BINDING_HTTP_REDIRECT))) {
                continue;
            }

            $assertionConsumerServices[$acs->index] = new IndexedService(
                $acs->Location,
                $acs->Binding,
                $acs->index,
                $acs->isDefault
            );
        }
        $entity->assertionConsumerServices = $assertionConsumerServices;
        return $entity;
    }
}
