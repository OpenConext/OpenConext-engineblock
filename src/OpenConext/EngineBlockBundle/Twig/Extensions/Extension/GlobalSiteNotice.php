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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use EngineBlock_ApplicationSingleton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Twig_Extension;

class GlobalSiteNotice extends Twig_Extension
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $application;

    /**
     * @var \EngineBlock_Application_DiContainer
     */
    private $diContainer;

    /**
     * @var null|Request
     */
    private $request;

    public function __construct(
        EngineBlock_ApplicationSingleton $application,
        RequestStack $requestStack
    ) {
        $this->application = $application;
        $this->diContainer = $application->getDiContainer();
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('shouldDisplayGlobalSiteNotice', [$this, 'shouldDisplayGlobalSiteNotice']),
            new TwigFunction('getGlobalSiteNotice', [$this, 'getGlobalSiteNotice']),
            new TwigFunction('getAllowedHtmlForNotice', [$this, 'getAllowedHtmlForNotice']),
        ];
    }

    public function shouldDisplayGlobalSiteNotice() : bool
    {
        if ($this->isTest()) {
            return (bool) $this->request->get('showGlobalSiteNotice', false);
        }

        return (bool) $this->diContainer->shouldDisplayGlobalSiteNotice();
    }

    public function getGlobalSiteNotice(): string
    {
        if ($this->isTest()) {
            $message = <<<MSG
<p>
    There is nothing wrong with your television set.
    <strong>Do not attempt to adjust the picture.</strong>
    We are controlling transmission. If we wish to make it louder, we will bring up the volume.
    If we wish to make it softer, we will tune it to a whisper. We will control the horizontal.
    We will control the vertical.  We can roll the image, make it flutter.
    We can change the focus to a soft blur, or sharpen it to crystal clarity.
</p>
<p>
    <strong>For the next hour, sit quietly and we will control all that you see and hear.</strong>
    We repeat: There is nothing wrong with your television set. You are about to participate in a great adventure.
    You are about to experience the awe and mystery which reaches from the inner mind to... The Outer Limits.
</p>
MSG;
            return (string) $this->request->get('globalSiteNotice', $message);
        }
        return (string) $this->diContainer->getGlobalSiteNotice();
    }

    public function getAllowedHtmlForNotice(): string
    {
        return (string) $this->diContainer->getAllowedHtmlForNotice();
    }

    private function isTest(): bool
    {
        $uri = $this->request->getRequestUri();
        return preg_match('/functional-testing/', $uri);
    }
}
