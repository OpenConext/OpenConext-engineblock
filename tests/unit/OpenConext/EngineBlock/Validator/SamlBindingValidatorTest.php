<?php

namespace OpenConext\EngineBlock\Validator;

use Mockery as m;
use OpenConext\EngineBlock\Exception\InvalidBindingException;
use OpenConext\EngineBlock\Exception\InvalidRequestMethodException;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\Request;

class SamlBindingValidatorTest extends TestCase
{
    /**
     * @var SamlBindingValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new SamlBindingValidator();

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

    public function test_post_binding_is_not_supported()
    {
        $this->expectException(InvalidRequestMethodException::class);
        $this->expectExceptionMessage('The HTTP request method "PATCH" is not supported on this SAML SSO endpoint');

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_PATCH);

        $this->validator->isValid($request);
    }

    public function test_used_invalid_binding()
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('No SAMLRequest parameter was found in the HTTP "GET" request parameters');

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

    public function test_used_unsupported_binding()
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('The binding type "SAML2\HTTPArtifact" is not supported on this SAML SSO endpoint');

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
