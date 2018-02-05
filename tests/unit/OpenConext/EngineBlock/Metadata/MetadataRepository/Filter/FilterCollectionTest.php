<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Class FilterCollectionTest
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class FilterCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testFilterRoleFailure()
    {
        $mockFilter = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface'
        );
        $mockFilter->shouldReceive('filterRole')->andReturnNull();
        $mockFilter->shouldReceive('__toString')->andReturn('MockFilter');


        $mockRole = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\Entity\AbstractRole'
        );

        $collection = new CompositeFilter();
        $collection->add($mockFilter);
        $this->assertNull($collection->filterRole($mockRole));
        $this->assertEquals('MockFilter', $collection->getDisallowedByFilter());
        $this->assertEquals('[MockFilter]', (string)$collection);
    }

    public function testFilterExport()
    {
        $mockFilter = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface'
        );
        $mockFilter->shouldReceive('toExpression')->andReturn(Criteria::expr()->isNull('entityId'));
        $mockFilter->shouldReceive('toQueryBuilder');

        $collection = new CompositeFilter();
        $collection->add($mockFilter);

        $this->assertTrue(
            $collection->toExpression(
                'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider'
            ) instanceof Expression
        );
        $this->assertTrue(
            $collection->toCriteria(
                'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider'
            ) instanceof Criteria
        );
        $queryBuilderMock = Mockery::mock('Doctrine\ORM\QueryBuilder');
        $this->assertEquals(
            $queryBuilderMock,
            $collection->toQueryBuilder(
                $queryBuilderMock,
                'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider'
            )
        );
    }
}
