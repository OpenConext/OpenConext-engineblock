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
use DOMNode;
use OpenConext\EngineBlock\Exception\InvalidSamlResponseException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The SamlResponseValidator verifies the validity of a SAML Response
 *
 * This is later tested in greater detail in the response processing service. This Validator can be seen as a sanity
 * check. And allows us to exit early out of the ACS step if the response is tempered with, or is not valid for other
 * reasons.
 *
 * Checks added in this validator have to be considered carefully as it might compromise performance (receive the
 * response multiple times per request)
 */
class SamlResponseValidator implements RequestValidator
{
    public function isValid(Request $request)
    {
        $document = $this->receive($request);

        // - Error responses do not have an assertion, but must be processed and are considered valid. They shall pass.
        // - Encrypted SAML responses are not decrypted here, and will be marked as having no assertion. It's okay
        //   to let them pass here. Further validation will take place when EngineBlock processes the SAML Response.
        if ($this->hasAssertion($document)) {
            $this->verifyHasOneAssertion($document);
            $this->verifyAssertionCorrectlySigned($document);
        }
        return true;
    }

    /**
     * Perform checks on the Signature portion of the Assertion
     *
     * @param DOMNode $document
     */
    private function verifyAssertionCorrectlySigned(DOMNode $document)
    {
        $assertion = $document->getElementsByTagName('Assertion')->item(0);
        $assertionId = $assertion->getAttribute('ID');
        $signatures = $assertion->getElementsByTagName('Signature');

        // Verify if the assertion is signed
        if ($signatures->count() > 0) {
            // Allow only one SignedInfo per Signature
            $signature = $signatures->item(0);

            // First child of the signature MUST be the SignedInfo element
            $signedInfo = $signature->firstChild;
            if ($signedInfo->localName !== 'SignedInfo') {
                throw new InvalidSamlResponseException('The first child of the Signature must be the SignedInfo element.');
            }

            // SignedInfo contains a Reference element which should have an URI attribute that matches the ID of the
            // assertion
            $referenceUri = $signedInfo->getElementsByTagName('Reference')->item(0)->getAttribute('URI');
            // Remove the hash from the URI, to compare it to the assertion id
            if (preg_match('/^\#?' . $assertionId . '$/', $referenceUri) !== 1) {
                throw new InvalidSamlResponseException('The Assertion ID must match the Reference URI value of the SignedInfo element.');
            }

            // The second child is the SignatureValue
            $signatureValue = $signature->childNodes->item(1);
            if ($signatureValue->localName !== 'SignatureValue') {
                throw new InvalidSamlResponseException('The second child of the Signature must be the SignatureValue element.');
            }

            // Verify there only is one SignedInfo element in the signature
            $signedInfoElements = $signature->getElementsByTagName('SignedInfo');
            if ($signedInfoElements->count() > 1) {
                throw new InvalidSamlResponseException('Only one SignedInfo element is allowed per Signature');
            }
        }
        // We allow unsigned assertions
    }

    /**
     * Verify if the response contains an Assertion
     *
     * @param DOMNode $document
     * @return bool
     */
    private function hasAssertion(DOMNode $document)
    {
        $assertions = $document->getElementsByTagName('Assertion');
        return $assertions->count() > 0;
    }

    /**
     * EngineBlock only allows the processing of a single assertion and by default we would use the first assertion.
     * As of now, posting back multiple assertions is considered illegal.
     *
     * @param DOMNode $document
     */
    private function verifyHasOneAssertion(DOMNode $document)
    {
        $assertions = $document->getElementsByTagName('Assertion');
        if ($assertions->count() > 1) {
            throw new InvalidSamlResponseException('Only one assertion is permitted per SAML Response in EngineBlock');
        }
    }

    private function receive(Request $request)
    {
        if ($request->request->has('SAMLResponse')) {
            $response = $request->request->get('SAMLResponse');
        } else {
            throw new InvalidSamlResponseException('Missing SAMLResponse parameter.');
        }

        $decodedResponse = base64_decode($response);

        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = false;

        $domDocument->loadXML($decodedResponse);
        return $domDocument->firstChild;
    }
}
