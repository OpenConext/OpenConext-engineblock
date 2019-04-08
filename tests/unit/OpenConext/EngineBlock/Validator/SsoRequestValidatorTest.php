<?php

namespace OpenConext\EngineBlock\Validator;

use OpenConext\EngineBlock\Exception\InvalidRequestMethodException;
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
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function test_happy_flow_get()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['SAMLRequest'] = 'loremipsum';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    public function test_happy_flow_post()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLRequest'] = 'loremipsum';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    public function test_post_binding_is_not_supported()
    {
        $this->expectException(InvalidRequestMethodException::class);
        $this->expectExceptionMessage('The HTTP request method "PATCH" is not supported on this SAML SSO endpoint');

        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }
}
