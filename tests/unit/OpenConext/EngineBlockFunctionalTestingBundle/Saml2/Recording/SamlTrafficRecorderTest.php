<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Recording;

use ErrorException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SamlTrafficRecorderTest extends TestCase
{
    private const XML = '<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="_x"/>';

    private SamlTrafficRecorder $recorder;

    private string $workDirectory;

    protected function setUp(): void
    {
        $this->workDirectory = sys_get_temp_dir() . '/saml-recorder-test-' . bin2hex(random_bytes(4));
        mkdir($this->workDirectory);
        $this->recorder = new SamlTrafficRecorder($this->workDirectory . '/marker.json');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->workDirectory);
    }

    public function testRecordsMessagesIntoTheScenarioDirectoryFromTheMarker(): void
    {
        file_put_contents(
            $this->workDirectory . '/marker.json',
            json_encode(['directory' => $this->workDirectory . '/out', 'scenario' => 'Feature_L12'])
        );

        $this->recorder->onKernelResponse($this->event(new RedirectResponse(
            'https://idp.example.org/sso?' . http_build_query(['SAMLRequest' => base64_encode((string) gzdeflate(self::XML))])
        )));

        $files = glob($this->workDirectory . '/out/Feature_L12/*.xml') ?: [];
        $this->assertCount(1, $files);
        $this->assertSame('001-SAMLRequest-engine.example.org_authn.xml', basename($files[0]));
        $this->assertSame(self::XML, file_get_contents($files[0]));
    }

    public function testRecordsNothingWithoutMarker(): void
    {
        $this->recorder->onKernelResponse($this->event(new RedirectResponse(
            'https://idp.example.org/sso?' . http_build_query(['SAMLRequest' => base64_encode((string) gzdeflate(self::XML))])
        )));

        $this->assertSame([], glob($this->workDirectory . '/*') ?: []);
    }

    private function event(Response $response): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://engine.example.org/authn'),
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );
    }

    public function testExtractsMessageFromHttpRedirect(): void
    {
        $query = http_build_query([
            'SAMLRequest' => base64_encode((string) gzdeflate(self::XML)),
            'RelayState' => 'abc',
        ]);

        $messages = $this->recorder->extractMessages(new RedirectResponse('https://idp.example.org/sso?' . $query));

        $this->assertSame(['SAMLRequest' => self::XML], $messages);
    }

    public function testExtractsMessageFromHttpPostForm(): void
    {
        $html = sprintf(
            '<html><body><form method="post" action="https://sp.example.org/acs">'
            . '<input type="hidden" name="SAMLResponse" value="%s"><input type="hidden" name="RelayState" value="x">'
            . '</form></body></html>',
            htmlspecialchars(base64_encode(self::XML))
        );

        $messages = $this->recorder->extractMessages(new Response($html, 200, ['Content-Type' => 'text/html']));

        $this->assertSame(['SAMLResponse' => self::XML], $messages);
    }

    public function testIgnoresResponsesWithoutSamlMessage(): void
    {
        $this->assertSame([], $this->recorder->extractMessages(new RedirectResponse('https://example.org/?a=b')));
        $this->assertSame(
            [],
            $this->recorder->extractMessages(new Response('<html>SAML is mentioned</html>', 200, ['Content-Type' => 'text/html']))
        );
    }

    public function testIgnoresParametersThatAreNotXml(): void
    {
        $messages = $this->recorder->extractMessages(
            new RedirectResponse('https://example.org/?SAMLRequest=' . base64_encode('not xml'))
        );

        $this->assertSame([], $messages);
    }

    public function testCorruptDeflatedRedirectPayloadIsIgnoredWithoutWarning(): void
    {
        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $messages = $this->recorder->extractMessages(
                new RedirectResponse('https://example.org/?SAMLRequest=' . rawurlencode(base64_encode("\x01\x02garbage")))
            );
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $messages);
    }
}
