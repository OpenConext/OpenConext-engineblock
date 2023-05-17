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

namespace OpenConext\EngineBlock\Metadata\Factory\Helper;

use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;

class IdentityProviderNameFallbackHelperTest extends AbstractEntityTest
{
    /**
     * @var IdentityProviderEntity|null
     */
    private $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = null;
    }

    /**
     * Verification test for display name related requirements stated in:
     * https://www.pivotaltracker.com/story/show/170401452
     */
    public function test_name_fallback()
    {
        // If Display Name EN is not set, the decorator will fall back on the Name EN
        $this->adapter = $this->createIdentityProviderAdapter(true);
        $decorator = new IdentityProviderNameFallbackHelper($this->adapter);

        // Falls back on the name EN when display name is not set (empty)
        $this->assertEquals($decorator->getName('en'), $decorator->getDisplayName('en'));

        // If Display Name NL is not set, the decorator will fall back on the Name NL
        $this->adapter = $this->createIdentityProviderAdapter(false, true);
        $decorator = new IdentityProviderNameFallbackHelper($this->adapter);

        // Falls back on the name NL when display name is not set (empty)
        $this->assertEquals($decorator->getName('nl'), $decorator->getDisplayName('nl'));

        // If Display Name NL is not set, the decorator will fall back on the Name NL
        $this->adapter = $this->createIdentityProviderAdapter(false, true, [
            'nameNl' => '',
        ]);
        $decorator = new IdentityProviderNameFallbackHelper($this->adapter);

        // Falls back on the name EN when display name and name NL are not set (empty)
        $this->assertEquals($decorator->getName('en'), $decorator->getDisplayName('nl'));
    }
}
