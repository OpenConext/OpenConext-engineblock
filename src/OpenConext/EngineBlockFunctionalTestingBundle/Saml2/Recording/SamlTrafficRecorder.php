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

use DOMDocument;
use DOMXPath;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SamlTrafficRecorder implements EventSubscriberInterface
{
    public const DIRECTORY_ENV = 'EB_SAML_RECORD_DIR';
    public const MARKER_FILE = '/tmp/eb-fixtures/saml-recording.json';

    private const MESSAGE_PARAMETERS = ['SAMLRequest', 'SAMLResponse'];

    public function __construct(private readonly string $markerFile = self::MARKER_FILE)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -1024]];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $recording = $this->readMarker();
        if ($recording === null) {
            return;
        }

        $messages = $this->extractMessages($event->getResponse());
        if ($messages === []) {
            return;
        }

        $request = $event->getRequest();
        $source = preg_replace('/[^A-Za-z0-9.]+/', '_', $request->getHost() . $request->getPathInfo());

        foreach ($messages as $type => $xml) {
            $this->write($recording['directory'], $recording['scenario'], $type, (string) $source, $xml);
        }
    }

    /**
     * @return array<string, string> SAML message type (SAMLRequest|SAMLResponse) => raw XML
     */
    public function extractMessages(Response $response): array
    {
        return $this->extractFromRedirect($response) + $this->extractFromForm($response);
    }

    /**
     * @return array<string, string>
     */
    private function extractFromRedirect(Response $response): array
    {
        $location = $response->headers->get('Location');
        if ($location === null) {
            return [];
        }

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $messages = [];
        foreach (self::MESSAGE_PARAMETERS as $parameter) {
            $xml = is_string($query[$parameter] ?? null) ? $this->decode($query[$parameter], true) : null;
            if ($xml !== null) {
                $messages[$parameter] = $xml;
            }
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    private function extractFromForm(Response $response): array
    {
        $content = (string) $response->getContent();
        if (!str_contains($content, 'SAML') || !str_contains((string) $response->headers->get('Content-Type'), 'html')) {
            return [];
        }

        $messages = [];
        foreach ($this->extractFormFields($content) as $parameter => $value) {
            $xml = $this->decode($value, false);
            if ($xml !== null) {
                $messages[$parameter] = $xml;
            }
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    private function extractFormFields(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $fields = [];
        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//input[@name="SAMLRequest" or @name="SAMLResponse"]') as $input) {
            $fields[$input->getAttribute('name')] = $input->getAttribute('value');
        }

        return $fields;
    }

    private function decode(string $encoded, bool $deflated): ?string
    {
        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return null;
        }

        if ($deflated) {
            $decoded = $this->inflate($decoded) ?? $decoded;
        }

        return str_starts_with(ltrim($decoded), '<') ? $decoded : null;
    }

    private function inflate(string $data): ?string
    {
        // Recording must never break the request, so a corrupt payload is ignored instead of raising a warning.
        set_error_handler(static fn(): bool => true);
        try {
            $inflated = gzinflate($data);
        } finally {
            restore_error_handler();
        }

        return $inflated === false ? null : $inflated;
    }

    /**
     * @return array{directory: string, scenario: string}|null
     */
    private function readMarker(): ?array
    {
        if (!is_file($this->markerFile)) {
            return null;
        }

        $marker = json_decode((string) file_get_contents($this->markerFile), true);
        if (!is_array($marker) || !is_string($marker['directory'] ?? null) || $marker['directory'] === '') {
            return null;
        }

        return [
            'directory' => $marker['directory'],
            'scenario' => is_string($marker['scenario'] ?? null) && $marker['scenario'] !== ''
                ? $marker['scenario']
                : 'unscoped',
        ];
    }

    private function write(string $directory, string $scenario, string $type, string $source, string $xml): void
    {
        $scenarioDirectory = $directory . '/' . $scenario;

        if (!is_dir($scenarioDirectory)) {
            mkdir($scenarioDirectory, 0777, true);
        }

        $sequence = count(glob($scenarioDirectory . '/*.xml') ?: []) + 1;

        file_put_contents(sprintf('%s/%03d-%s-%s.xml', $scenarioDirectory, $sequence, $type, $source), $xml);
    }
}
