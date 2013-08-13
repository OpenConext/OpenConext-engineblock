
<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class DummyIdp_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setNoRender();

        $samlRequest = $this->decodeSamlRequest($this->getSamlRequest());

        $authNRequestDomElement = $this->authnRequestToDomElement($samlRequest);

        require_once ENGINEBLOCK_FOLDER_LIBRARY . 'simplesamlphp/lib/_autoload.php';
        $authnRequest = SAML2_AuthnRequest::fromXML($authNRequestDomElement);

        $samlResponse = $this->factorySaml2PResponse($authnRequest);

        $formHtml = $this->factoryForm($samlResponse);

        // @todo find out what correct content type is
        header('Content-Type: text/html');
        echo $formHtml;
        exit;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getSamlRequest()
    {
        $samlRequestEncoded = $this->_getRequest()->getQueryParameter('SAMLRequest');
        if (empty($samlRequestEncoded)) {
            throw new Exception('No SAMLRrquest Attribute');
        }

        return $samlRequestEncoded;
    }

    /**
     * @param string $samlRequestEncoded
     * @return string
     */
    private function decodeSamlRequest($samlRequestEncoded)
    {
        return gzinflate(base64_decode($samlRequestEncoded));
    }

    /**
     * @param string $samlRequest
     * @return DOMNode
     */
    private function authnRequestToDomElement($samlRequest)
    {
        $document = new DOMDocument();
        $document->loadXML($samlRequest);
        return $document->getElementsByTagNameNs('urn:oasis:names:tc:SAML:2.0:protocol', 'AuthnRequest')->item(0);
    }

    /**
     * @param int $time
     * @return string
     */
    private function formatTime($time)
    {
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    private function factorySaml2PResponse(
        SAML2_AuthnRequest $authnRequest
    )
    {
        $engineBlockApp = EngineBlock_ApplicationSingleton::getInstance();
        $config = $engineBlockApp->getConfiguration();
        $encryptionConfig = $config->get('encryption')->toArray();

        $sspIdpConfig = array();
        $privateKeyPath = tempnam(sys_get_temp_dir(), 'ssp_private_key');
        file_put_contents($privateKeyPath, $encryptionConfig['key']['private']);
        $sspIdpConfig['privatekey'] = $privateKeyPath;

        $publicKeyPath = tempnam(sys_get_temp_dir(), 'ssp_public_key');
        file_put_contents($publicKeyPath, $encryptionConfig['key']['public']);
        $sspIdpConfig['publickey'] = $publicKeyPath;

        $idpMetadata = new SimpleSAML_Configuration($sspIdpConfig, null);

        $spMetadata = new SimpleSAML_Configuration(array(), null);

        /* $returnAttributes contains the attributes we should return. Send them. */
        $assertion = new SAML2_Assertion();
        $assertion->setIssuer($authnRequest->getIssuer());
        // @todo get this from constant
        $assertion->setNameId(array(
            'Format' => "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
        ));
        $assertion->setNotBefore(time());
        $assertion->setNotOnOrAfter(time() + 5*60);
        $assertion->setValidAudiences(array($authnRequest->getIssuer()));

        // Add a few required attributes
        $returnAttributes = array(
            'urn:mace:dir:attribute-def:uid' => array('johndoe'),
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => array('example.com'),
        );
        $assertion->setAttributes($returnAttributes);
        $assertion->setAttributeNameFormat(SAML2_Const::NAMEFORMAT_UNSPECIFIED);

        $subjectConfirmation = new SAML2_XML_saml_SubjectConfirmation();
        $subjectConfirmation->Method = SAML2_Const::CM_BEARER;
        $subjectConfirmation->SubjectConfirmationData = new SAML2_XML_saml_SubjectConfirmationData();
        $subjectConfirmation->SubjectConfirmationData->NotOnOrAfter = time() + 5*60;
// @todo set these values
//        $subjectConfirmation->SubjectConfirmationData->Recipient = $endpoint;
        $subjectConfirmation->SubjectConfirmationData->InResponseTo = $authnRequest->getId();
        $assertion->setSubjectConfirmation(array($subjectConfirmation));
        sspmod_saml_Message::addSign($idpMetadata, $spMetadata, $assertion);

        $response = new SAML2_Response();
        $response->setRelayState($authnRequest->getRelayState());
        $response->setDestination($authnRequest->getDestination());
        $response->setIssuer($authnRequest->getIssuer());
        $response->setInResponseTo($authnRequest->getId());
        $response->setAssertions(array($assertion));
        sspmod_saml_Message::addSign($idpMetadata, $spMetadata, $response);

        $samlResponse = $response->toSignedXML();
        $samlResponseXml = $samlResponse->ownerDocument->saveXML($samlResponse);

        return $samlResponseXml;
    }

    /**
     * @param string $samlResponse
     * @return string
     */
    private function factoryForm($samlResponse)
    {
        $samlResponseEncoded = base64_encode($samlResponse);

        $formHtml = <<<FORM_HTML
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <body onload="document.forms[0].submit()">
        <noscript>
            <p>
                <strong>Note:</strong> Since your browser does not support JavaScript,
                you must press the Continue button once to proceed.
            </p>
        </noscript>
        <!-- @todo make action dynamic -->
        <form action="https&#x3a;&#x2f;&#x2f;engine-test.demo.openconext.org&#x2f;authentication&#x2f;sp&#x2f;consume-assertion" method="post">
            <div>
                <!--PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c2FtbDJwOlJlc3BvbnNlIHhtbG5zOnNhbWwycD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOnByb3RvY29sIiBEZXN0aW5hdGlvbj0iaHR0cHM6Ly9lbmdpbmUuZGVtby5vcGVuY29uZXh0Lm9yZy9hdXRoZW50aWNhdGlvbi9zcC9jb25zdW1lLWFzc2VydGlvbiIgSUQ9IjM4ZTkzZDQyLTM1ZmEtNGM4Yy05NmRlLWUxYjk1YjFlNGFiMyIgSW5SZXNwb25zZVRvPSJDT1JUT2M4N2U0MjVlNzJjYWZmYjFlMmE1NDMzYTIxNDZjODExMDhmMDgyMjMiIElzc3VlSW5zdGFudD0iMjAxMy0wOC0xMlQxNjo0NzoyMS4wNjFaIiBWZXJzaW9uPSIyLjAiPjxzYW1sMjpJc3N1ZXIgeG1sbnM6c2FtbDI9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphc3NlcnRpb24iIEZvcm1hdD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOm5hbWVpZC1mb3JtYXQ6ZW50aXR5Ij5odHRwOi8vbW9jay1pZHA8L3NhbWwyOklzc3Vlcj48c2FtbDJwOlN0YXR1cz48c2FtbDJwOlN0YXR1c0NvZGUgVmFsdWU9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpzdGF0dXM6U3VjY2VzcyIvPjwvc2FtbDJwOlN0YXR1cz48c2FtbDI6QXNzZXJ0aW9uIHhtbG5zOnNhbWwyPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YXNzZXJ0aW9uIiBJRD0iMTQ0ZTI1OGUtOWFlNC00YTg1LWFlM2YtYTAyYTY5OTEwNjRjIiBJc3N1ZUluc3RhbnQ9IjIwMTMtMDgtMTJUMTY6NDc6MjAuOTU2WiIgVmVyc2lvbj0iMi4wIiB4bWxuczp4cz0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEiPjxzYW1sMjpJc3N1ZXIgRm9ybWF0PSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6bmFtZWlkLWZvcm1hdDplbnRpdHkiPmh0dHA6Ly9tb2NrLWlkcDwvc2FtbDI6SXNzdWVyPjxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPjxkczpTaWduZWRJbmZvPjxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+PGRzOlNpZ25hdHVyZU1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNyc2Etc2hhMSIvPjxkczpSZWZlcmVuY2UgVVJJPSIjMTQ0ZTI1OGUtOWFlNC00YTg1LWFlM2YtYTAyYTY5OTEwNjRjIj48ZHM6VHJhbnNmb3Jtcz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI2VudmVsb3BlZC1zaWduYXR1cmUiLz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIj48ZWM6SW5jbHVzaXZlTmFtZXNwYWNlcyB4bWxuczplYz0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIiBQcmVmaXhMaXN0PSJ4cyIvPjwvZHM6VHJhbnNmb3JtPjwvZHM6VHJhbnNmb3Jtcz48ZHM6RGlnZXN0TWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3NoYTEiLz48ZHM6RGlnZXN0VmFsdWU+ejVHeDRPRDN3cWtBL0NaaG9sTjZWK2lWMHRrPTwvZHM6RGlnZXN0VmFsdWU+PC9kczpSZWZlcmVuY2U+PC9kczpTaWduZWRJbmZvPjxkczpTaWduYXR1cmVWYWx1ZT55U0k0ZS9DcEFkbStSUjlMU3lwT2ZkRnFWZUEzYmVwWnlkd3NKS3U2eDI3emRHT1RDMTJwUSsvWE5sY2FIQjlXNXVGRW5KYU9vd2R2UGxjajZqYmdnMG0vbXJORmdDby8wam9VZ0lKOC9Qa0VUT1NPY3YvWEpOTGJLZTNrTWxLclhpRVFRa2FMeWdFVnJMWVV0VmEyVlRrZzkxK3BoUkIybzhRellmMzl2Q0U9PC9kczpTaWduYXR1cmVWYWx1ZT48L2RzOlNpZ25hdHVyZT48c2FtbDI6U3ViamVjdD48c2FtbDI6TmFtZUlEIEZvcm1hdD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6MS4xOm5hbWVpZC1mb3JtYXQ6dW5zcGVjaWZpZWQiPmE8L3NhbWwyOk5hbWVJRD48c2FtbDI6U3ViamVjdENvbmZpcm1hdGlvbiBNZXRob2Q9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpjbTpiZWFyZXIiPjxzYW1sMjpTdWJqZWN0Q29uZmlybWF0aW9uRGF0YSBBZGRyZXNzPSIxOTIuMTY4LjU2LjEiIEluUmVzcG9uc2VUbz0iQ09SVE9jODdlNDI1ZTcyY2FmZmIxZTJhNTQzM2EyMTQ2YzgxMTA4ZjA4MjIzIiBOb3RPbk9yQWZ0ZXI9IjIwMTMtMDgtMTJUMTY6NDg6NTAuOTU1WiIgUmVjaXBpZW50PSJodHRwczovL2VuZ2luZS5kZW1vLm9wZW5jb25leHQub3JnL2F1dGhlbnRpY2F0aW9uL3NwL2NvbnN1bWUtYXNzZXJ0aW9uIi8+PC9zYW1sMjpTdWJqZWN0Q29uZmlybWF0aW9uPjwvc2FtbDI6U3ViamVjdD48c2FtbDI6QXV0aG5TdGF0ZW1lbnQgQXV0aG5JbnN0YW50PSIyMDEzLTA4LTEyVDE2OjQ3OjIwLjg2NFoiPjxzYW1sMjpBdXRobkNvbnRleHQ+PHNhbWwyOkF1dGhuQ29udGV4dENsYXNzUmVmPnVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphYzpjbGFzc2VzOlBhc3N3b3JkPC9zYW1sMjpBdXRobkNvbnRleHRDbGFzc1JlZj48c2FtbDI6QXV0aGVudGljYXRpbmdBdXRob3JpdHk+aHR0cDovL21vY2staWRwPC9zYW1sMjpBdXRoZW50aWNhdGluZ0F1dGhvcml0eT48L3NhbWwyOkF1dGhuQ29udGV4dD48L3NhbWwyOkF1dGhuU3RhdGVtZW50PjxzYW1sMjpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWwyOkF0dHJpYnV0ZSBOYW1lPSJ1cm46bWFjZTpkaXI6YXR0cmlidXRlLWRlZjp1aWQiPjxzYW1sMjpBdHRyaWJ1dGVWYWx1ZSB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4c2k6dHlwZT0ieHM6c3RyaW5nIj5hPC9zYW1sMjpBdHRyaWJ1dGVWYWx1ZT48L3NhbWwyOkF0dHJpYnV0ZT48c2FtbDI6QXR0cmlidXRlIE5hbWU9InVybjpvaWQ6MS4zLjYuMS40LjEuMTA3Ni4yMC4xMDAuMTAuMTAuMSI+PHNhbWwyOkF0dHJpYnV0ZVZhbHVlIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiIHhzaTp0eXBlPSJ4czpzdHJpbmciPmd1ZXN0PC9zYW1sMjpBdHRyaWJ1dGVWYWx1ZT48L3NhbWwyOkF0dHJpYnV0ZT48c2FtbDI6QXR0cmlidXRlIE5hbWU9InVybjptYWNlOmRpcjphdHRyaWJ1dGUtZGVmOnNuIj48c2FtbDI6QXR0cmlidXRlVmFsdWUgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgeHNpOnR5cGU9InhzOnN0cmluZyI+RG9lPC9zYW1sMjpBdHRyaWJ1dGVWYWx1ZT48L3NhbWwyOkF0dHJpYnV0ZT48c2FtbDI6QXR0cmlidXRlIE5hbWU9InVybjptYWNlOmRpcjphdHRyaWJ1dGUtZGVmOm1haWwiPjxzYW1sMjpBdHRyaWJ1dGVWYWx1ZSB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4c2k6dHlwZT0ieHM6c3RyaW5nIj5qLmRvZUBleGFtcGxlLmNvbTwvc2FtbDI6QXR0cmlidXRlVmFsdWU+PC9zYW1sMjpBdHRyaWJ1dGU+PHNhbWwyOkF0dHJpYnV0ZSBOYW1lPSJ1cm46bWFjZTpkaXI6YXR0cmlidXRlLWRlZjplZHVQZXJzb25QcmluY2lwYWxOYW1lIj48c2FtbDI6QXR0cmlidXRlVmFsdWUgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgeHNpOnR5cGU9InhzOnN0cmluZyI+ai5kb2VAZXhhbXBsZS5jb208L3NhbWwyOkF0dHJpYnV0ZVZhbHVlPjwvc2FtbDI6QXR0cmlidXRlPjxzYW1sMjpBdHRyaWJ1dGUgTmFtZT0idXJuOm1hY2U6ZGlyOmF0dHJpYnV0ZS1kZWY6ZGlzcGxheU5hbWUiPjxzYW1sMjpBdHRyaWJ1dGVWYWx1ZSB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4c2k6dHlwZT0ieHM6c3RyaW5nIj5hPC9zYW1sMjpBdHRyaWJ1dGVWYWx1ZT48L3NhbWwyOkF0dHJpYnV0ZT48c2FtbDI6QXR0cmlidXRlIE5hbWU9InVybjptYWNlOmRpcjphdHRyaWJ1dGUtZGVmOmdpdmVuTmFtZSI+PHNhbWwyOkF0dHJpYnV0ZVZhbHVlIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiIHhzaTp0eXBlPSJ4czpzdHJpbmciPkpvaG48L3NhbWwyOkF0dHJpYnV0ZVZhbHVlPjwvc2FtbDI6QXR0cmlidXRlPjxzYW1sMjpBdHRyaWJ1dGUgTmFtZT0idXJuOm1hY2U6dGVyZW5hLm9yZzphdHRyaWJ1dGUtZGVmOnNjaGFjSG9tZU9yZ2FuaXphdGlvbiI+PHNhbWwyOkF0dHJpYnV0ZVZhbHVlIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiIHhzaTp0eXBlPSJ4czpzdHJpbmciPmV4YW1wbGUuY29tPC9zYW1sMjpBdHRyaWJ1dGVWYWx1ZT48L3NhbWwyOkF0dHJpYnV0ZT48c2FtbDI6QXR0cmlidXRlIE5hbWU9InVybjptYWNlOmRpcjphdHRyaWJ1dGUtZGVmOmNuIj48c2FtbDI6QXR0cmlidXRlVmFsdWUgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgeHNpOnR5cGU9InhzOnN0cmluZyI+Sm9obiBEb2U8L3NhbWwyOkF0dHJpYnV0ZVZhbHVlPjwvc2FtbDI6QXR0cmlidXRlPjwvc2FtbDI6QXR0cmlidXRlU3RhdGVtZW50Pjwvc2FtbDI6QXNzZXJ0aW9uPjwvc2FtbDJwOlJlc3BvbnNlPg==-->
                <input type="hidden" name="SAMLResponse" value="$samlResponseEncoded"/>
            </div>
            <noscript>
                <div>
                    <input type="submit" value="Continue"/>
                </div>
            </noscript>
        </form>
    </body>
</html>
FORM_HTML;

        return $formHtml;

    }
}
