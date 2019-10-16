<?php declare(strict_types=1);

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

namespace OpenConext\EngineBlockBundle\Url;

use InvalidArgumentException;
use OpenConext\EngineBlockBundle\Exception\UnableToCreateUrlException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlProvider
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string $name The route name identifier as can be found in the routing configuration
     * @param bool $processingMode When disabled, the keyId and remoteEntityId parameters will be added to the URL
     * @param string|null $keyId
     * @param string|null $remoteEntityId
     * @return string
     */
    public function getUrl(string $name, bool $processingMode, ?string $keyId, ?string $remoteEntityId): string
    {
        try {
            // Build the absolute URL based on the route name
            $url = $this->urlGenerator->generate($name, [], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (InvalidArgumentException $e) {
            throw new UnableToCreateUrlException($e->getMessage());
        }

        // Append the key identifier
        if (!$processingMode && $keyId && $name === 'authentication_idp_sso') {
            $url .= '/key:' . $keyId;
        }

        // Append the Transparent identifier
        if ($remoteEntityId && !$processingMode && $name !== 'metadata_idp' && $name !== 'authentication_logout') {
            $url .= '/' . md5($remoteEntityId);
        }

        return $url;
    }
}
