<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service\ConsentProvider;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Saml2_NameIdResolver;
use EngineBlock_SamlHelper;
use OpenConext\EngineBlock\Consent\Consent;
use OpenConext\EngineBlock\Consent\ConsentMap;
use OpenConext\EngineBlock\Message\RequestId;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ConsentFactoryInterface;
use OpenConext\EngineBlock\Service\ConsentServiceInterface;
use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use SAML2\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

/**
 * Ask the user for consent over all of the attributes being sent to the SP.
 */
class ConsentProvider
{
    /**
     * @var ConsentProviderProxyServerInterface
     */
    private $proxyServer;

    /**
     * @var ConsentFactoryInterface
     */
    private $consentFactory;

    /**
     * @var ConsentServiceInterface
     */
    private $consentService;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $authenticationStateHelper;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        ConsentFactoryInterface $consentFactory,
        ConsentServiceInterface $consentService,
        AuthenticationStateHelperInterface $stateHelper,
        RequestStack $request,
        Environment $twig
    ) {
        $this->proxyServer = null;
        $this->consentFactory = $consentFactory;
        $this->consentService = $consentService;
        $this->authenticationStateHelper = $stateHelper;
        $this->request = $request->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->twig = $twig;
    }

    public function setProxyServer(ConsentProviderProxyServerInterface $proxyServer)
    {
        $this->proxyServer = $proxyServer;
    }

    public function serve()
    {
        if (is_null($this->proxyServer)) {
            throw new RuntimeException('Before using the service, the current proxy server must be set');
        }

        $response = $this->proxyServer->getBindingsModule()->receiveResponse();

        $this->saveResponseToSession($response);

        $request = $this->proxyServer->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->proxyServer->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $spMetadataChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $serviceProvider,
            $request,
            $this->proxyServer->getRepository()
        );

        $identityProviderEntityId = $response->getOriginalIssuer();
        $identityProvider = $this->proxyServer->getRepository()->fetchIdentityProviderByEntityId(
            $identityProviderEntityId
        );

        // Flush log if SP or IdP has additional logging enabled
        $requireAdditionalLogging = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(
            array_merge($spMetadataChain, array($identityProvider))
        );
        if ($requireAdditionalLogging) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->flushLog(
                'Activated additional logging for one or more SPs in the SP requester chain, or the IdP'
            );

            $log = $application->getLogInstance();
            $log->info('Raw HTTP request', array('http_request' => (string)$application->getHttpRequest()));
        }
        $serviceProviderMetadata = $spMetadataChain[0];

        $attributes = $response->getAssertion()->getAttributes();
        $consentRepository = $this->consentFactory->create($this->proxyServer, $response, $attributes);

        $authenticationState = $this->authenticationStateHelper->getAuthenticationState();

        if ($this->isConsentDisabled($spMetadataChain, $identityProvider)) {
            if (!$consentRepository->implicitConsentWasGivenFor($serviceProviderMetadata)) {
                $consentRepository->giveImplicitConsentFor($serviceProviderMetadata);
            }

            $response->setConsent(Constants::CONSENT_INAPPLICABLE);
            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            // Consent is disabled, we now mark authentication_state as completed
            $authenticationState->completeCurrentProcedure($response->getInResponseTo());

            $this->proxyServer->getBindingsModule()->send(
                $response,
                $serviceProvider
            );

            return;
        }

        $priorConsent = $consentRepository->explicitConsentWasGivenFor($serviceProviderMetadata);
        if ($priorConsent) {
            $response->setConsent(Constants::CONSENT_PRIOR);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            // Prior consent is found, we now mark authentication_state as completed
            $authenticationState->completeCurrentProcedure($response->getInResponseTo());

            $this->proxyServer->getBindingsModule()->send(
                $response,
                $serviceProvider
            );

            return;
        }

        $settings = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        // Profile url is configurable in application.ini (profile.baseUrl)
        $profileUrl = '#';
        $configuredUrl = $settings->getProfileBaseUrl();
        if (!empty($configuredUrl)) {
            $profileUrl = $configuredUrl;
        }

        // If attribute manipulation was executed before consent, the SetNameId filter has already been applied, and
        // applying the NameIdResolver is not required.
        $featureConfiguration = $settings->getFeatureConfiguration();
        $amPriorToConsent = $featureConfiguration->isEnabled('eb.run_all_manipulations_prior_to_consent');

        // Show the correctly formatted nameId on the consent screen
        $isPersistent = $serviceProvider->nameIdFormat === Constants::NAMEID_PERSISTENT;

        // Create a local copy of the NameID that is set on the response. We do not yet want to update the actual NameID
        // in the $response yet as this will cause side effects when saving the consent entry. The 'hashed user id'
        // will not be consistent.
        $nameId = clone $response->getNameId();

        if ($isPersistent && !$amPriorToConsent) {
            $collabPersonIdValue = $nameId->value;
            // Load the persistent name id for this combination of SP/Identifier and update the local copy of the nameId
            // to ensure the correct identifier is shown on the consent screen.
            $resolver = new EngineBlock_Saml2_NameIdResolver();
            $nameId = $resolver->resolve(
                $request,
                $response,
                $serviceProvider,
                $collabPersonIdValue
            );
        }

        // The nameId format is not yet updated on the response (will be performed in the SetNameId filter after
        // consent), but in order to display the correct nameId format, we set the SP requested name Id format on the
        // name id copy that is used to render the correct identifier on the consent page. If AM was already performed,
        // use the nameIdFormat from the nameId, and do not overwrite it with the SP's preferred format.
        if (!$amPriorToConsent) {
            $nameId->Format = $serviceProvider->nameIdFormat;
        }

        $html = $this->twig->render(
            '@theme/Authentication/View/Proxy/consent.html.twig',
            [
                'action' => $this->proxyServer->getUrl('processConsentService'),
                'responseId' => $response->getId(),
                'sp' => $serviceProviderMetadata,
                'idp' => $identityProvider,
                'idpSupport' => $this->getSupportContact($identityProvider),
                'attributes' => $attributes,
                'attributeSources' => $this->getAttributeSources($request->getId()),
                'attributeMotivations' => $this->getAttributeMotivations($serviceProviderMetadata, $attributes),
                'minimalConsent' => $identityProvider->getConsentSettings()->isMinimal(
                    $serviceProviderMetadata->entityId
                ),
                'consentCount' => $this->consentService->countAllFor($response->getNameIdValue()),
                'nameId' => $nameId,
                'nameIdSupportUrl' => $settings->getOpenConextNameIdSupportUrl(),
                'nameIdIsPersistent' => $isPersistent,
                'profileUrl' => $profileUrl,
                'supportUrl' => $settings->getOpenConextSupportUrl(),
                'showConsentExplanation' => $identityProvider->getConsentSettings()->hasConsentExplanation(
                    $serviceProviderMetadata->entityId
                ),
                'consentSettings' => $identityProvider->getConsentSettings(),
                'spEntityId' => $serviceProviderMetadata->entityId,
                'hideHeader' => true,
                'hideFooter' => true,
            ]
        );

        $this->proxyServer->sendOutput($html);
    }

    /**
     * @param ServiceProvider[] $serviceProviders
     * @param IdentityProvider $identityProvider
     * @return bool
     */
    private function isConsentDisabled(array $serviceProviders, IdentityProvider $identityProvider)
    {
        foreach ($serviceProviders as $serviceProvider) {
            if (!$serviceProvider->isConsentRequired) {
                return true;
            }

            if (!$identityProvider->getConsentSettings()->isEnabled($serviceProvider->entityId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find attribute sources in the session.
     *
     * The attribute aggregator corto filter stores the aggregated attribute
     * sources in the session.
     */
    private function getAttributeSources($requestId)
    {
        if (isset($_SESSION[$requestId]['attribute_sources'])) {
            return $_SESSION[$requestId]['attribute_sources'];
        }

        return [];
    }

    /**
     * Find motivations in SP ARP for all given attributes.
     *
     * @param ServiceProvider $sp
     * @param array $attributes
     * @return array
     */
    private function getAttributeMotivations(ServiceProvider $sp, array $attributes)
    {
        $motivations = [];

        foreach (array_keys($attributes) as $attributeName) {
            $motivations[$attributeName] = $this->getAttributeMotivation($sp, $attributeName);
        }

        return array_filter($motivations);
    }

    /**
     * Find motivation text in SP ARP for given attribute.
     *
     * @param ServiceProvider $sp
     * @param string $attributeName
     * @return string
     */
    private function getAttributeMotivation(ServiceProvider $sp, $attributeName)
    {
        $arp = $sp->getAttributeReleasePolicy();
        if ($arp === null) {
            return null;
        }

        return $arp->getMotivation($attributeName);
    }

    /**
     * @param IdentityProvider $idp
     * @return ContactPerson
     */
    private function getSupportContact(IdentityProvider $idp)
    {
        foreach ($idp->contactPersons as $contact) {
            if ($contact->contactType === 'support') {
                return $contact;
            }
        }
    }

    private function saveResponseToSession(\EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        if (!$this->session->has('consent')) {
            $this->session->set('consent', new ConsentMap());
        }

        $consent = new Consent($response);
        $requestId = new RequestId($response->getSspMessage()->getId());

        $this->session->get('consent')->add($requestId, $consent);
        $this->session->save();
    }
}
