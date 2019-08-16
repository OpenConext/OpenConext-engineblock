<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * Class FilterCollection
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Helper
 */
class CompositeVisitor implements VisitorInterface
{
    /**
     * @var VisitorInterface[]
     */
    private $visitors = array();

    /**
     * @param VisitorInterface $visitor
     * @return $this
     */
    public function append(VisitorInterface $visitor)
    {
        $this->visitors[] = $visitor;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function visitIdentityProvider(IdentityProvider $identityProvider)
    {
        foreach ($this->visitors as $visitor) {
            $visitor->visitIdentityProvider($identityProvider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function visitServiceProvider(ServiceProvider $serviceProvider)
    {
        foreach ($this->visitors as $visitor) {
            $visitor->visitServiceProvider($serviceProvider);
        }
    }
}
