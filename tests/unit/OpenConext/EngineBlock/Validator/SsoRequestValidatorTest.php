<?php

namespace OpenConext\EngineBlock\Validator;

use Mockery as m;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;
use OpenConext\EngineBlockBundle\Authentication\Entity\ServiceProviderUuid;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\Request;

class SsoRequestValidatorTest extends TestCase
{
    /**
     * @var SsoRequestValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new SsoRequestValidator();

        // PHPunit does not reset the superglobals on each run.
        unset($_GET['SAMLRequest']);
        unset($_GET['AuhtNRequest']);
        unset($_GET['SAMLart']);
        unset($_POST['SAMLRequest']);
    }

    public function test_happy_flow_get()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['SAMLRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);
        $this->assertTrue($this->validator->isValid($request));
    }

    public function test_happy_flow_post()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_POST);
        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidRequestMethodException
     * @expectedExceptionMessage The HTTP request method "PATCH" is not supported on this SAML SSO endpoint
     */
    public function test_post_binding_is_not_supported()
    {
        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_PATCH);

        $this->validator->isValid($request);
    }

    /**
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidBindingException
     * @expectedExceptionMessage No SAMLRequest parameter was found in the HTTP "GET" request parameters
     */
    public function test_used_invalid_binding()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Even though we are handling a AuthNRequest, the binding is based on a get parameter named SAMLRequest
        $_GET['AuhtNRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);

        $this->validator->isValid($request);
    }

    /**
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidBindingException
     * @expectedExceptionMessage The binding type "SAML2\HTTPArtifact" is not supported on this SAML SSO endpoint
     */
    public function test_used_unsupported_binding()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // EB does not support artifact binding
        $_GET['SAMLart'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);

        $this->validator->isValid($request);
    }
}
