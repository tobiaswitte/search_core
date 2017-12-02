<?php
namespace Codappix\SearchCore\Tests\Unit\Domain\Model;

/*
 * Copyright (C) 2017  Daniel Siepmann <coding@daniel-siepmann.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use Codappix\SearchCore\Domain\Model\SearchRequest;
use Codappix\SearchCore\Tests\Unit\AbstractUnitTestCase;

class SearchRequestTest extends AbstractUnitTestCase
{
    /**
     * @test
     * @dataProvider possibleEmptyFilter
     */
    public function emptyFilterWillNotBeSet(array $filter)
    {
        $searchRequest = new SearchRequest();
        $searchRequest->setFilter($filter);

        $this->assertSame(
            [],
            $searchRequest->getFilter(),
            'Empty filter were set, even if they should not.'
        );
    }

    public function possibleEmptyFilter()
    {
        return [
            'Complete empty Filter' => [
                'filter' => [],
            ],
            'Single filter with empty value' => [
                'filter' => [
                    'someFilter' => '',
                ],
            ],
            'Single filter with empty recursive values' => [
                'filter' => [
                    'someFilter' => [
                        'someKey' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function filterIsSet()
    {
        $filter = ['someField' => 'someValue'];
        $searchRequest = new SearchRequest();
        $searchRequest->setFilter($filter);

        $this->assertSame(
            $filter,
            $searchRequest->getFilter(),
            'Filter was not set.'
        );
    }
}
