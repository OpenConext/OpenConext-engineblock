<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class MetadataControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->clearMetadataFixtures();

        parent::__construct($name, $data, $dataName);

    }

    public function tearDown()
    {
        $this->clearMetadataFixtures();
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_get_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request(
            $invalidHttpMethod,
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function authentication_is_required_for_getting_metadata_for_idp()
    {
        $entityId = 'https://test-idp.test';

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     * @group FeatureToggle
     */
    public function cannot_get_an_idps_metadata_if_the_feature_has_been_disabled()
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function cannot_get_an_idps_metadata_if_user_does_not_have_profile_role()
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     *
     * @dataProvider invalidEntityIdProvider
     * @param string $invalidEntityId
     */
    public function cannot_get_metadata_for_an_idp_if_an_invalid_entity_id_has_been_given($invalidEntityId)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$invalidEntityId,
            [],
            [],
            []
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function cannot_get_metadata_if_idp_cannot_be_found()
    {
        $unknownIdpEntityId = 'https://unknown-idp.test';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$unknownIdpEntityId,
            [],
            [],
            []
        );
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function idp_metadata_can_be_requested_from_the_metadata_api()
    {
        $idpEntityId = 'https://my-idp.test';

        $enOrganizationName = 'My Org (EN)';
        $enOrganizationDisplayName = 'My Organization';
        $enOrganizationUrl = 'https://my-idp.test/en';
        $enOrganization = new Organization($enOrganizationName, $enOrganizationDisplayName, $enOrganizationUrl);

        $nlOrganizationName = 'My Org (NL)';
        $nlOrganizationDisplayName = 'Mijn organisatie';
        $nlOrganizationUrl = 'https://my-idp.test/nl';
        $nlOrganization = new Organization($nlOrganizationName, $nlOrganizationDisplayName, $nlOrganizationUrl);

        $ptOrganizationName = 'A Minha Org (PT)';
        $ptOrganizationDisplayName = 'A Minha Organização';
        $ptOrganizationUrl = 'https://my-idp.test/pt';
        $ptOrganization = new Organization($ptOrganizationName, $ptOrganizationDisplayName, $ptOrganizationUrl);

        $sloLocation = 'https://my-idp.test/slo';
        $sloBinding  = Constants::BINDING_HTTP_POST;
        $sloService = new Service($sloLocation, $sloBinding);

        $supportContactType = 'support';
        $supportContactEmail = 'support@my-idp.test';
        $supportContact = new ContactPerson($supportContactType);
        $supportContact->emailAddress = $supportContactEmail;

        $enDescription = 'Description';
        $nlDescription = 'Omschrijving';
        $ptDescription = 'Descrição';

        $enDisplayName = 'My IdP';
        $nlDisplayName = 'Mijn IdP';
        $ptDisplayName = 'O Meu IdP';

        $enName = 'My IdP (EN)';
        $nlName = 'My IdP (NL)';
        $ptName = 'O Meu Idp (PT)';

        $logoHeight = 100;
        $logoWidth = 100;
        $logoUrl = 'https://my-idp.test/logo.png';
        $logo = new Logo($logoUrl);
        $logo->height = $logoHeight;
        $logo->width = $logoWidth;

        $shibMdRegex = false;
        $shibMdAllowed = 'abc';
        $shibMdScope = new ShibMdScope();
        $shibMdScope->regexp = $shibMdRegex;
        $shibMdScope->allowed = $shibMdAllowed;

        $ssoLocation = 'https://my-idp.test/sso';
        $ssoBinding  = Constants::BINDING_HTTP_POST;
        $ssoService = new Service($ssoLocation, $ssoBinding);

        $idp = $this->createIdentityProvider(
            $idpEntityId,
            $enOrganization,
            $nlOrganization,
            $ptOrganization,
            $sloService,
            $supportContact,
            $enDescription,
            $nlDescription,
            $ptDescription,
            $enDisplayName,
            $nlDisplayName,
            $ptDisplayName,
            $logo,
            $enName,
            $nlName,
            $ptName,
            $shibMdScope,
            $ssoService
        );
        $this->addIdentityProviderFixture($idp);

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$idpEntityId,
            [],
            [],
            []
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);

        $expectedData = [
            'entity_id' => $idpEntityId,
            'organization' => [
                'en' => [
                    'name' => $enOrganizationName,
                    'display_name' => $enOrganizationDisplayName,
                    'url' => $enOrganizationUrl,
                ],
                'nl' => [
                    'name' => $nlOrganizationName,
                    'display_name' => $nlOrganizationDisplayName,
                    'url' => $nlOrganizationUrl,
                ],
                'pt' => [
                    'name' => $ptOrganizationName,
                    'display_name' => $ptOrganizationDisplayName,
                    'url' => $ptOrganizationUrl,
                ],
            ],
            'single_logout_service' => [
                'binding' => $sloBinding,
                'location' => $sloLocation,
            ],
            'contact_persons' => [
                [
                    'contact_type' => $supportContactType,
                    'email_address' => $supportContactEmail,
                    'telephone_number' => ''
                ]
            ],
            'description' => [
                'en' => $enDescription,
                'nl' => $nlDescription,
                'pt' => $ptDescription,
            ],
            'display_name' => [
                'en' => $enDisplayName,
                'nl' => $nlDisplayName,
                'pt' => $ptDisplayName,
            ],
            'name' => [
                'en' => $enName,
                'nl' => $nlName,
                'pt' => $ptName,
            ],
            'logo' => [
                'height' => $logoHeight,
                'width' => $logoWidth,
                'url' => $logoUrl,
            ],
            'shib_md_scopes' => [
                [
                    'regexp' => $shibMdRegex,
                    'allowed' => $shibMdAllowed,
                ],
            ],
            'single_sign_on_services' => [
                [
                    'binding' => $ssoBinding,
                    'location' => $ssoLocation,
                ]
            ]
        ];
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($expectedData, $responseData);

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

    public function invalidEntityIdProvider()
    {
        return [
            'empty string' => [''],
            'null' => [null]
        ];
    }

    private function enableMetadataApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_api' => new Feature('api.metadata_api', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableMetadataApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_api' => new Feature('api.metadata_api', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function addIdentityProviderFixture(IdentityProvider $identityProvider)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($identityProvider);
        $em->flush();
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }

    /**
     * @param $idpEntityId
     * @param Organization $enOrganization
     * @param Organization $nlOrganization
     * @param Organization $ptOrganization
     * @param Service $sloService
     * @param ContactPerson $supportContact
     * @param $enDescription
     * @param $nlDescription
     * @param $ptDescription
     * @param $enDisplayName
     * @param $nlDisplayName
     * @param $ptDisplayName
     * @param Logo $logo
     * @param $enName
     * @param $nlName
     * @param $ptName
     * @param ShibMdScope $shibMdScope
     * @param Service $ssoService
     * @return IdentityProvider
     */
    private function createIdentityProvider(
        $idpEntityId,
        Organization $enOrganization,
        Organization $nlOrganization,
        Organization $ptOrganization,
        Service $sloService,
        ContactPerson $supportContact,
        $enDescription,
        $nlDescription,
        $ptDescription,
        $enDisplayName,
        $nlDisplayName,
        $ptDisplayName,
        Logo $logo,
        $enName,
        $nlName,
        $ptName,
        ShibMdScope $shibMdScope,
        Service $ssoService
    ) {
        $idp = new IdentityProvider(
            $idpEntityId,
            $enOrganization,
            $nlOrganization,
            $ptOrganization,
            $sloService,
            false,
            [],
            [$supportContact],
            $enDescription,
            $nlDescription,
            $ptDescription,
            false,
            $enDisplayName,
            $nlDisplayName,
            $ptDisplayName,
            '',
            '',
            '',
            $logo,
            $enName,
            $nlName,
            $ptName,
            null,
            [Constants::NAMEID_TRANSIENT, Constants::NAMEID_PERSISTENT],
            null,
            false,
            false,
            XMLSecurityKey::RSA_SHA1,
            null,
            IdentityProvider::WORKFLOW_STATE_DEFAULT,
            '',
            null,
            true,
            IdentityProvider::GUEST_QUALIFIER_ALL,
            false,
            null,
            [$shibMdScope],
            [$ssoService]
        );

        return $idp;
    }
}
