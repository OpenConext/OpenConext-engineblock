<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Twig_Extension;

/**
 * Used to retrieve the version of a specified User agent can be found in the HTTP_USER_AGENT super global via the
 * request stack that is injected on the Extension.
 */
class UserAgent extends Twig_Extension
{
    /**
     * @var string
     */
    private $httpUserAgent;

    public function __construct(RequestStack $requestStack)
    {
        $this->httpUserAgent = $this->getUserAgent($requestStack);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'userAgent',
                [$this, 'userAgent']
            )
        ];
    }

    public function userAgent($isOfType)
    {
        switch ($isOfType) {
            case 'safari':
                $pattern = '~Version/(\d+)[\\.\d]+ Safari/~';
                return preg_match($pattern, $this->httpUserAgent, $matches) ? 'safari' . $matches[1] : '';
                break;
            default:
                throw new RuntimeException(
                    sprintf(
                        'This user agent ("%s") is not yet supported in the UserAgent Twig extension',
                        $isOfType
                    )
                );
        }
    }

    private function getUserAgent(RequestStack $requestStack)
    {
        $currentRequest = $requestStack->getCurrentRequest();
        $userAgent = '';
        if ($currentRequest) {
            $userAgent = $currentRequest->server->get('HTTP_USER_AGENT', '');
        }
        return $userAgent;
    }
}
