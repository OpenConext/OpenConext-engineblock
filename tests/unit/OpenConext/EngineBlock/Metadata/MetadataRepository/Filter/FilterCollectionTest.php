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
        $mockFilter->shouldReceive('toQueryBuilder');

        $collection = new CompositeFilter();
        $collection->add($mockFilter);

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
