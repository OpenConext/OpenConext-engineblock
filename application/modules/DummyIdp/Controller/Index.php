
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

require_once ENGINEBLOCK_FOLDER_LIBRARY . 'simplesamlphp/lib/_autoload.php';

class DummyIdp_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $authnRequestFactory = new EngineBlock_Saml_AuthnRequestFactory();
        $authnRequest = $authnRequestFactory->createFromHttpRequest($this->_getRequest());

        $responseFactory = new EngineBlock_Saml_ResponseFactory();
        $samlResponse = $responseFactory->create($authnRequest);

        $samlMessageSerializer = new EngineBlock_Saml_MessageSerializer();
        $samlResponseXml = $samlMessageSerializer->serialize($samlResponse);

        $formHtml = $this->factoryForm($samlResponseXml, $authnRequest->getAssertionConsumerServiceURL());

        $this->setNoRender();
        header('Content-Type: text/html');
        echo $formHtml;
        exit;
    }

    /**
     * @param string $samlResponse
     * @param string $assertionConsumerServiceUrl
     * @return string
     */
    private function factoryForm($samlResponse, $assertionConsumerServiceUrl)
    {
        $samlResponseEncoded = base64_encode($samlResponse);
        $assertionConsumerServiceUrlEncoded = htmlspecialchars($assertionConsumerServiceUrl);

        $formHtml = <<<FORM_HTML
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <body onload="document.forms[0].submit()">
        <form action="$assertionConsumerServiceUrlEncoded" method="post">
            <input type="hidden" name="SAMLResponse" value="$samlResponseEncoded"/>
            <input type="submit" value="Continue"/>
        </form>
    </body>
</html>
FORM_HTML;

        return $formHtml;
    }
}
