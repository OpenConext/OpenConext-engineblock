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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;
use SAML2\Response;

/**
 * @todo test all other functionalities of Bindings, currently tests a small part of redirection
 */
class EngineBlock_Test_Corto_Module_BindingsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Corto_Module_Bindings
     */
    private $bindings;

    public function setUp(): void
    {
        $proxyServer = Phake::mock('EngineBlock_Corto_ProxyServer');
        Phake::when($proxyServer)->getSigningCertificates(false)->thenReturn(
            new EngineBlock_X509_KeyPair(
                new EngineBlock_X509_Certificate(openssl_x509_read(file_get_contents(__DIR__.'/test.pem.crt'))),
                new EngineBlock_X509_PrivateKey(__DIR__.'/test.pem.key')
            )
        );
        $this->bindings = new EngineBlock_Corto_Module_Bindings($proxyServer);
    }

    public function testResponseRedirectIsNotSupported()
    {
        $this->expectException(EngineBlock_Corto_Module_Bindings_UnsupportedBindingException::class);

        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator(new Response());
        $response->setDeliverByBinding(Constants::BINDING_HTTP_REDIRECT);

        $remoteEntity = new ServiceProvider('https://sp.example.edu');
        $this->bindings->send($response, $remoteEntity);
    }

    /**
     * We specifically test for some error / validation messages to have a certain value,
     * and throw a custom exception if that is the case. For example we did this to show a
     * custom user facing error page when the received SAML Response contains an assertion
     * from the past or future.
     *
     * This test simply verifies if the error message that is yielded from the SAML2 library
     * did not change, effectively changing behaviour of EB.
     *
     * Disclaimer, this is not a pure unit test in the sense it tests a specific feature of
     * Bindings. This however seemed the most logical place to put it.
     */
    public function test_saml2_library_error_messages_we_specifically_test_have_not_changed()
    {
        $assertion = m::mock(Assertion::class);
        $assertion
            ->shouldReceive('getNotBefore')
            // Unix timestamp: 9000000000 translates to: 11/20/2286 @ 5:46pm (UTC)
            ->andReturn(9999999999);

        $assertion
            ->shouldReceive('getNotOnOrAfter')
            // Unix timestamp: 1 translates to: 01/01/1970 @ 12:00am (UTC)
            ->andReturn(1);

        $result = new Result();
        $notBefore = new NotBefore();
        $notOnOrAfter = new NotOnOrAfter();

        $notBefore->validate($assertion, $result);
        $this->assertEquals(
            'Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.',
            $result->getErrors()[0]
        );

        $notOnOrAfter->validate($assertion, $result);
        $this->assertEquals(
            'Received an assertion that has expired. Check clock synchronization on IdP and SP.',
            $result->getErrors()[1]
        );
    }

    /**
     * Provides a list of paths to response xml files and certificate files
     *
     * @return array
     */
    public function responseProvider()
    {
        $responseFiles = array();
        $certificateFiles = array();
        $responsesDir = TEST_RESOURCES_DIR.'/saml/responses';
        $defaultCertFile = $responsesDir.'/defaultCert';
        $responsesDirIterator = new DirectoryIterator($responsesDir);
        /** @var $responseFile DirectoryIterator */
        foreach ($responsesDirIterator as $responseFile) {
            if ($responseFile->isFile() && !$responseFile->isDot()) {
                $extension = substr($responseFile->getFilename(), -3);
                $fileNameWithoutExtension = substr($responseFile->getFilename(), 0, -4);

                if ($extension == 'cer' || $extension == 'pem') {
                    $certificateFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();
                } elseif ($extension == 'xml') {
                    $responseFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();

                    // Set default certificate, can be overridden by adding a certificate with the same name as the response
                    if (!isset($certificateFiles[$fileNameWithoutExtension])) {
                        $certificateFiles[$fileNameWithoutExtension] = $defaultCertFile;
                    }
                }
            }
        }

        return array_merge_recursive($responseFiles, $certificateFiles);
    }
}
