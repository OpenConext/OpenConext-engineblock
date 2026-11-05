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

namespace OpenConext\EngineBlockBundle\Tests\EventListener;

use OpenConext\EngineBlockBundle\EventListener\SessionHandlerInitializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionHandlerInitializerTest extends TestCase
{
    #[Test]
    public function it_creates_the_session_without_starting_it()
    {
        $session = new Session(new MockArraySessionStorage());
        $created = 0;
        $request = new Request();
        $request->setSessionFactory(function () use ($session, &$created) {
            $created++;
            return $session;
        });

        (new SessionHandlerInitializer())->onKernelRequest($this->event($request, HttpKernelInterface::MAIN_REQUEST));

        $this->assertSame(1, $created);
        $this->assertFalse($session->isStarted());
    }

    #[Test]
    public function it_ignores_sub_requests()
    {
        $created = 0;
        $request = new Request();
        $request->setSessionFactory(function () use (&$created) {
            $created++;
            return new Session(new MockArraySessionStorage());
        });

        (new SessionHandlerInitializer())->onKernelRequest($this->event($request, HttpKernelInterface::SUB_REQUEST));

        $this->assertSame(0, $created);
    }

    #[Test]
    public function it_ignores_requests_without_a_session()
    {
        $request = new Request();

        (new SessionHandlerInitializer())->onKernelRequest($this->event($request, HttpKernelInterface::MAIN_REQUEST));

        $this->assertFalse($request->hasSession());
    }

    private function event(Request $request, int $type): RequestEvent
    {
        return new RequestEvent($this->createStub(HttpKernelInterface::class), $request, $type);
    }
}

