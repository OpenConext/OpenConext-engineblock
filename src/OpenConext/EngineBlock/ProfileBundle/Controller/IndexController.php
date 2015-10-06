<?php

namespace OpenConext\EngineBlock\ProfileBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Arp_AttributeReleasePolicyEnforcer;
use EngineBlock_Attributes_Normalizer;
use EngineBlock_User;
use EngineBlock_View;
use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\ProfileBundle\Service\AttributeFilter;
use Surfnet_Zend_Auth_Adapter_Saml;
use Symfony\Component\HttpFoundation\Request;

class IndexController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;
    /**
     * @var AttributeFilter
     */
    private $attributeFilter;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        AttributeFilter $attributeFilter
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView = $engineBlockView;
        $this->attributeFilter = $attributeFilter;
    }

    public function indexAction(Request $request)
    {
        $viewData = array();
        $authenticationHelper = new Surfnet_Zend_Auth_Adapter_Saml();
        $authenticationResult = $authenticationHelper->authenticate();

        $user               = new EngineBlock_User($authenticationResult->getIdentity());
        $pimpleContainer    = $this->engineBlockApplicationSingleton->getDiContainer();
        $consentedToSps     = $user->getConsent();
        $availableSps       = $pimpleContainer->getServiceRegistryClient()->getSpList();
        $filteredAttributes = $this->attributeFilter->filter($user->getAttributes());

        $viewData['userAttributes']   = $filteredAttributes;
        $viewData['entityId']         = $authenticationHelper->getEntityId();
        $viewData['metadata']         = $pimpleContainer->getAttributeMetadata();
        $viewData['spList']           = $availableSps;
        $viewData['consent']          = $consentedToSps;
        $viewData['spAttributesList'] = $this->getFilteredAttributesAllowedForSps(
            $consentedToSps,
            $availableSps,
            $filteredAttributes
        );
        $viewData['mailSend']         = $request->get('mailSend');
    }

    /**
     * Returns an array with attributes that are released to each SP.
     *
     * We check if there is an ARP and then return this otherwise all attributes we have gotten.
     *
     * @param array $spList all service providers
     * @param array $availableSps
     * @param array $attributes
     * @return array with service providers Id's with the ARP
     */
    protected function getFilteredAttributesAllowedForSps(array $spList, array $availableSps, array $attributes)
    {
        $normalizer           = new EngineBlock_Attributes_Normalizer($attributes);
        $normalizedAttributes = $normalizer->normalize();
        unset($normalizedAttributes['nameid']);

        $results               = array();
        $serviceRegistryClient = $this->engineBlockApplicationSingleton->getDiContainer()->getServiceRegistryClient();
        $enforcer              = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        foreach ($spList as $spId) {
            if (!isset($availableSps[$spId])) {
                continue;
            }

            $arp = $serviceRegistryClient->getArp($spId);

            if (empty($arp)) {
                continue;
            }

            $results[$spId] = $enforcer->enforceArp(
                new AttributeReleasePolicy($arp['attributes']), $normalizedAttributes
            );
        }

        return $results;
    }
}
