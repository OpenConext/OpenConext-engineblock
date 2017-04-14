<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

final class ConsentControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function authentication_is_required_for_accessing_the_consent_api()
    {
        $userId = 1111;

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_get_requests_are_allowed_when_accessing_the_consent_api($invalidHttpMethod)
    {
        $userId = 1111;

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     * @group FeatureToggle
     */
    public function cannot_access_the_consent_api_if_the_feature_has_been_disabled()
    {
        $userId = 1111;

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function cannot_access_the_consent_api_if_user_does_not_have_profile_role()
    {
        $userId = 1111;

        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    public function invalidHttpMethodProvider()
    {
        return [
            'POST' => ['POST'],
            'DELETE' => ['DELETE'],
            'HEAD' => ['HEAD'],
            'PUT' => ['PUT'],
            'OPTIONS' => ['OPTIONS']
        ];
    }

    private function enableConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_listing' => new Feature('api.consent_listing', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_listing' => new Feature('api.consent_listing', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }
}
