<?php

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

    /**
     * {@inheritdoc}
     */
    public function visitRole(AbstractRole $role)
    {
        foreach ($this->visitors as $visitor) {
            $visitor->visitRole($role);
        }
    }
}
