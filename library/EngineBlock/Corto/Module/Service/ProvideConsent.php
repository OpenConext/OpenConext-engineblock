<?php
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\ConsentServiceInterface;
use SAML2\Constants;
use Twig\Environment;

/**
 * Ask the user for consent over all of the attributes being sent to the SP.
 *
 * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
 */
class EngineBlock_Corto_Module_Service_ProvideConsent
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    private $_server;
    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    private $_xmlConverter;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private  $_consentFactory;

    /** @var ConsentServiceInterface */
    private  $_consentService;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        ConsentServiceInterface $consentService,
        Environment $twig
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
        $this->_consentService = $consentService;
        $this->twig = $twig;
    }

    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response->getId()]['response'] = $response;

        $request = $this->_server->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $spMetadataChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $serviceProvider,
            $request,
            $this->_server->getRepository()
        );

        $identityProviderEntityId = $response->getOriginalIssuer();
        $identityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($identityProviderEntityId);

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
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }
        $serviceProviderMetadata = $spMetadataChain[0];

        $attributes = $response->getAssertion()->getAttributes();
        $consentRepository = $this->_consentFactory->create($this->_server, $response, $attributes);

        if ($this->isConsentDisabled($spMetadataChain, $identityProvider)) {
            if (!$consentRepository->implicitConsentWasGivenFor($serviceProviderMetadata)) {
                $consentRepository->giveImplicitConsentFor($serviceProviderMetadata);
            }

            $response->setConsent(Constants::CONSENT_INAPPLICABLE);
            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
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

            $this->_server->getBindingsModule()->send(
                $response,
                $serviceProvider
            );
            return;
        }

        // Profile url is configurable in application.ini (profile.baseUrl)
        $profileUrl = '#';
        $configuredUrl = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getProfileBaseUrl();
        if (!empty($configuredUrl)) {
            $profileUrl = $configuredUrl;
        }

        $html = $this->twig->render(
            '@theme/Authentication/View/Proxy/consent.html.twig',
            [
                'action' => $this->_server->getUrl('processConsentService'),
                'responseId' => $response->getId(),
                'sp' => $serviceProviderMetadata,
                'idp' => $identityProvider,
                'idpSupport' => $this->getSupportContact($identityProvider),
                'attributes' => $attributes,
                'attributeSources' => $this->getAttributeSources($request->getId()),
                'consentCount' => $this->_consentService->countAllFor($response->getNameIdValue()),
                'profileUrl' => $profileUrl,
                'hideHeader' => true,
                'hideFooter' => true,
            ]
        );

        $this->_server->sendOutput($html);
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

            if (in_array($serviceProvider->entityId, $identityProvider->spsEntityIdsWithoutConsent)) {
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
     * @return ContactPerson|null
     */
    public function getSupportContact(IdentityProvider $idp)
    {
        foreach ($idp->contactPersons as $contact) {
            if ($contact->contactType === 'support') {
                return $contact;
            }
        }
    }
}
