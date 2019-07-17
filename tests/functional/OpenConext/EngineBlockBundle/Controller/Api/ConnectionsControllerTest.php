<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ConnectionsControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     */
    public function authentication_is_required_for_pushing_metadata()
    {
        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_post_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     * @group FeatureToggle
     */
    public function cannot_push_metadata_if_feature_is_disabled()
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->disableMetadataPushApiFeatureFor($client);

        $client->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     */
    public function cannot_push_metadata_if_user_does_not_have_manage_role()
    {
        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        $client->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     * @dataProvider invalidJsonPayloadProvider
     * @param string $invalidJsonPayload
     */
    public function cannot_push_invalid_content_to_the_metadata_push_api($invalidJsonPayload)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/api/connections',
            [],
            [],
            [],
            $invalidJsonPayload
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     */
    public function pushing_data_to_engineblock_should_succeed()
    {
        $this->clearMetadataFixtures();

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        foreach ($this->validConnectionsData() as $step) {

            $payload = $this->createJsonData($step);

            $client->request(
                'POST',
                'https://engine-api.vm.openconext.org/api/connections',
                [],
                [],
                [],
                $payload
            );
            $this->assertStatusCode(Response::HTTP_OK, $client);

            // check content type
            $isContentTypeJson = $client->getResponse()->headers->contains('Content-Type', 'application/json');
            $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

            // check response status
            $body = $client->getResponse()->getContent();
            $result = json_decode($body, true);
            $this->assertTrue($result['success']);

            // validate data
            $metadata = $this->getStoredMetadata();
            $this->assertNotEmpty($metadata);

            foreach ($step as $role) {
                $this->assertArrayHasKey($role['entityId'], $metadata);

                $data = $metadata[$role['entityId']];
                $this->assertSame($role['entityId'], $data->entityId);
                $this->assertSame($role['name'], $data->nameEn);

                switch($role['type']) {
                    case 'saml20-idp':
                        $this->assertInstanceOf(IdentityProvider::class, $data);
                        break;
                    case 'saml20-sp':
                        $this->assertInstanceOf(ServiceProvider::class, $data);
                        break;
                    default:
                        throw new \Exception('Unknown role type encountered');
                }

                unset($metadata[$role['entityId']]);
            }

            $this->assertEmpty($metadata);
        }
    }


    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     */
    public function pushing_data_with_coins_to_engineblock_should_succeed()
    {
        $this->clearMetadataFixtures();

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        foreach ($this->validConnectionsWithCoinsData() as $connection) {

            $payload = $this->createJsonData([$connection]);

            $client->request(
                'POST',
                'https://engine-api.vm.openconext.org/api/connections',
                [],
                [],
                [],
                $payload
            );
            $this->assertStatusCode(Response::HTTP_OK, $client);

            // check content type
            $isContentTypeJson = $client->getResponse()->headers->contains('Content-Type', 'application/json');
            $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

            // check response status
            $body = $client->getResponse()->getContent();
            $result = json_decode($body, true);
            $this->assertTrue($result['success']);

            // validate data
            $metadata = $this->getStoredMetadata();

            $this->assertArrayHasKey($connection['entityId'], $metadata);

            $data = $metadata[$connection['entityId']];
            $this->assertSame($connection['entityId'], $data->entityId);

            // validate coins
            foreach ($connection['expected-coins'] as $key => $value) {
                $this->assertSame($value, $data->$key, "Coin value for '{$key}' expected to be '{$value}' but unexpected '{$data->$key}' encountered.");
            }
        }
    }

    public function invalidHttpMethodProvider()
    {
        return [
            'GET' => ['GET'],
            'DELETE' => ['DELETE'],
            'HEAD' => ['HEAD'],
            'PUT' => ['PUT'],
            'OPTIONS' => ['OPTIONS']
        ];
    }

    public function invalidJsonPayloadProvider()
    {
        return [
            'string body' => ['"a-string"'],
            'integer body' => ['123'],
            'array body' => ['["an-array"]'],
            'empty object body' => ['{}'],
            'string connections' => ['{connections: "a-string"}'],
            'integer connections' => ['{connections: 1}'],
            'array connections' => ['{connections: ["a", "b", "c"]'],
        ];
    }

    private function enableMetadataPushApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_push' => new Feature('api.metadata_push', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableMetadataPushApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_push' => new Feature('api.metadata_push', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }

    /**
     * @return ServiceProvider[]|IdentityProvider[]
     */
    private function getStoredMetadata()
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $doctrine->getManager()->clear();

        $results = [];

        $idp = $doctrine->getManager()->getRepository(ServiceProvider::class)->findAll();
        foreach ($idp as $role) {
            $results[$role->entityId] = $role;
        }

        $sp = $doctrine->getManager()->getRepository(IdentityProvider::class)->findAll();
        foreach ($sp as $role) {
            $results[$role->entityId] = $role;
        }

        return $results;
    }

    private function createJsonData($connections)
    {
        $connectionsJson = [];
        foreach ($connections as $data) {
            $connectionsJson[] = $this->createPayloadConnectionJson($data['uuid'], $data['entityId'], $data['name'], $data['type'], $data['coins']);
        }
        $connectionsJson = implode(',', $connectionsJson);

        return sprintf('{"connections":{%s}}', $connectionsJson);
    }

    private function createPayloadConnectionJson($uuid, $entityId, $name, $type, $coins = [])
    {
        $coinsJson = '';
        if (!empty($coins)) {
            $coinsJson = '"coin": ' . json_encode($coins) . ',';
        }

        return sprintf('"%1$s":{
            "allow_all_entities":true,
            "allowed_connections":[],
            "metadata":{
                %5$s
                "name":{
                    "en":"%3$s"
                    }
                },
            "name":"%2$s",
            "state":"prodaccepted",
            "type":"%4$s"
        }',
            $uuid,
            $entityId,
            $name,
            $type,
            $coinsJson);
    }

    private function validConnectionsData()
    {
        return [
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'entityId' => 'https://my-idp.test/1',
                    'name' => 'SP0',
                    'type' => 'saml20-sp',
                ],
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1',
                    'type' => 'saml20-sp',
                ],[
                    'uuid' => '00000000-0000-0000-0000-000000000002',
                    'entityId' => 'https://my-idp.test/3',
                    'name' => 'IDP3',
                    'type' => 'saml20-idp',
                ],
            ],
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'entityId' => 'https://my-idp.test/1',
                    'name' => 'SP0 updated',
                    'type' => 'saml20-sp',
                ],
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1 updated',
                    'type' => 'saml20-sp',
                ],[
                    'uuid' => '00000000-0000-0000-0000-000000000002',
                    'entityId' => 'https://my-idp.test/3',
                    'name' => 'IDP3 updated',
                    'type' => 'saml20-idp',
                ],
            ],
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1',
                    'type' => 'saml20-sp',
                ],
            ],
        ];
    }


    private function validConnectionsWithCoinsData()
    {
        return [
            [
                'uuid' => '00000000-0000-0000-0000-000000000000',
                'entityId' => 'https://my-idp.test/1',
                'name' => 'SP0',
                'type' => 'saml20-sp',
                'coins' => [
                    'no_consent_required' => '0',
                    'transparant_issuer' => '1',
                    'trusted_proxy' => '1',
                    'display_unconnected_idps_wayf' => '0',
                    'eula' => 'eula',
                    'do_not_add_attribute_aliases' => '1',
                    'policy_enforcement_decision_required' => '0',
                    'requesterid_required' => '1',
                    'sign_response' => '0',
                    // abstract
                    'publish_in_edugain' => '1',
                    'disable_scoping' => '0',
                    'additional_logging' => '1',
                    'signature_method' => 'signature-method',
                ],
                'expected-coins' => [
                    'isConsentRequired' => true,
                    'isTransparentIssuer' => true,
                    'isTrustedProxy' => true,
                    'displayUnconnectedIdpsWayf' => false,
                    'termsOfServiceUrl' => 'eula',
                    'skipDenormalization' => true,
                    'policyEnforcementDecisionRequired' => false,
                    'requesteridRequired' => true,
                    'signResponse' => false,
                    // abstract
                    'publishInEdugain' => true,
                    'disableScoping' => false,
                    'additionalLogging' => true,
                    'signatureMethod' => 'signature-method',
                ]
            ],
            [
                'uuid' => '00000000-0000-0000-0000-000000000001',
                'entityId' => 'https://my-idp.test/2',
                'name' => 'IDP1',
                'type' => 'saml20-idp',
                'coins' => [
                    'guest_qualifier' => 'guest-qualifier',
                    'schachomeorganization' => 'schac-home-organization',
                    'hidden' => '0',
                    // abstract
                    'publish_in_edugain' => '1',
                    'disable_scoping' => '0',
                    'additional_logging' => '1',
                    'signature_method' => 'signature-method',
                ],
                'expected-coins' => [
                    'guestQualifier' => 'guest-qualifier',
                    'schacHomeOrganization' => 'schac-home-organization',
                    'hidden' => false,
                    // abstract
                    'publishInEdugain' => true,
                    'disableScoping' => false,
                    'additionalLogging' => true,
                    'signatureMethod' => 'signature-method',
                ]
            ],
            [
                'uuid' => '00000000-0000-0000-0000-000000000002',
                'entityId' => 'https://my-idp.test/1',
                'name' => 'SP0',
                'type' => 'saml20-sp',
                'coins' => [
                    'transparant_issuer' => true,
                    'trusted_proxy' => false,
                    'display_unconnected_idps_wayf' => '1',
                    'requesterid_required' => '0',
                    'policy_enforcement_decision_required' => '-1',
                ],
                'expected-coins' => [
                    'isTransparentIssuer' => true,
                    'isTrustedProxy' => false,
                    'displayUnconnectedIdpsWayf' => true,
                    'requesteridRequired' => false,
                    'policyEnforcementDecisionRequired' => true,
                ]
            ],
        ];
    }
}
