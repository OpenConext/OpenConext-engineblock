<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Validator;

use DOMDocument;
use OpenConext\EngineBlock\Exception\InvalidSamlResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SamlResponseValidatorTest extends TestCase
{
    /**
     * @var SamlBindingValidator
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new SamlResponseValidator();

        // PHPunit does not reset the superglobals on each run.
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    /**
     * @backupGlobals enabled
     */
    public function test_happy_flow()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // The uu.xml document contains a valid SAMLResponse
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('uu.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * A SAML response without Assertion is not validated, and will be investigated in greater detail when processing
     * the response.
     *
     * @backupGlobals enabled
     */
    public function test_assertion_less_saml_responses_are_passsed()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // The uu.xml document contains a valid SAMLResponse
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('adfsStatusResponder.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * A SAML response without Assertion is not validated, and will be investigated in greater detail when processing
     * the response.
     *
     * @backupGlobals enabled
     */
    public function test_encrypted_assertion_saml_responses_are_passsed()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // The uu.xml document contains a valid SAMLResponse
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('encrypted_assertion.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * Finding H01 from the EngineBlock SFO audit performed by Hackmanit
     *
     * XML Signature Validation Bypass
     *
     * In a nutshell:
     * The SAML validation logic can be circumvented by inserting an additional SignedInfo element, which contains a
     * valid reference, but is not covered by the signature computation.
     *
     * See the audit report for more details, but review the h01_response.xml to see how this was exploitable.
     *
     * @backupGlobals enabled
     */
    public function test_tampering_with_saml_response_is_rejected()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('h01_response.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('Only one assertion is permitted per SAML Response in EngineBlock');
        $this->validator->isValid($request);
    }

    /**
     * EngineBlock currently only allows SAMLResponses with one assertion.
     * @backupGlobals enabled
     */
    public function test_two_assertions_are_disallowed()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('multiple_assertions_response.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('Only one assertion is permitted per SAML Response in EngineBlock');
        $this->validator->isValid($request);
    }

    /**
     * The SignedInfo element should be the first child of the Signature element.
     * @backupGlobals enabled
     */
    public function test_the_signed_info_element_must_be_first_child_of_signature()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('signed_info_not_first_child.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('The first child of the Signature must be the SignedInfo element');
        $this->validator->isValid($request);
    }

    /**
     * The Reference element URI attribute should refer to the Assertion ID it is part of.
     *
     * @backupGlobals enabled
     */
    public function test_the_reference_uri_should_match_assertion_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources(
            'assertion_id_does_not_match_signature_reference_uri.xml'
        );

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('he Assertion ID must match the Reference URI value of the SignedInfo element');
        $this->validator->isValid($request);
    }

    /**
     * The second child of the signature element should be the signature value element
     *
     * @backupGlobals enabled
     */
    public function test_signed_value_should_be_signatures_second_child()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('signature_value_not_second_child.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('The second child of the Signature must be the SignatureValue element');
        $this->validator->isValid($request);
    }

    /**
     * Using multiple SignedInfo elements in a Signature is not allowed
     * @backupGlobals enabled
     */
    public function test_only_one_signed_info_element_should_be_permitted()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = $this->loadSamlResponseFromResources('multiple_signed_info_elements.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('Only one SignedInfo element is allowed per Signature');
        $this->validator->isValid($request);
    }

    /**
     * Only the SAMLResponse POST parameter is used to receive a SAMLResponse
     *
     * @backupGlobals enabled
     */
    public function test_reads_saml_response_from_post()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['Respons'] = $this->loadSamlResponseFromResources('uu.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('Missing SAMLResponse parameter');
        $this->validator->isValid($request);
    }

    /**
     * Other input validators should have already picked this up.
     * HTTP Redirect binding is not allowed.
     * @backupGlobals enabled
     */
    public function test_reads_saml_response_from_post_http_binding_not_processed()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['SAMLRespons'] = $this->loadSamlResponseFromResources('uu.xml');

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->expectException(InvalidSamlResponseException::class);
        $this->expectExceptionMessage('Missing SAMLResponse parameter');
        $this->validator->isValid($request);
    }

    private function loadSamlResponseFromResources($name)
    {
        $path = __DIR__ . '/../../../../../';
        $pathFromRoot = 'tests/resources/saml/responses/';

        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = false;
        $domDocument->load($path . $pathFromRoot . $name);
        $domDocument->formatOutput = false;
        $contents = $domDocument->saveXML();
        return base64_encode($contents);
    }
}
