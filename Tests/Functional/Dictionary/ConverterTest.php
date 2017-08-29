<?php
namespace Vendor\Tests\Functional\Dictionary;

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

use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use Codappix\SearchCore\Dictionary\Converter;
use Codappix\SearchCore\Tests\Functional\AbstractFunctionalTestCase;
use Codappix\SearchCore\Utility\FileUtility;

class ConverterTest extends AbstractFunctionalTestCase
{
    /**
     * @var Converter
     */
    protected $subject;

    /**
     * @var ConfigurationContainerInterface
     */
    protected $configuration;

    public function setUp()
    {
        parent::setUp();

        $this->configuration = $this->getMockBuilder(ConfigurationContainerInterface::class)
            ->getMock();
        $this->subject = new Converter(
            new FileUtility(),
            $this->configuration
        );
    }

    public function tearDown()
    {
        unlink($this->getFixtureFile('Dictionary/OpenthesaurusTemp.txt'));

        parent::tearDown();
    }

    /**
     * @test
     */
    public function openthesaurusFileIsConvertedAsExpected()
    {
        $inputFile = $this->getFixtureFile('Dictionary/OpenthesaurusInput.txt');
        $outputFile = $this->getFixtureFile('Dictionary/OpenthesaurusTemp.txt');
        $expectedOutputFile = $this->getFixtureFile('Dictionary/OpenthesaurusOutput.txt');

        $this->configuration->expects($this->once())
            ->method('get')
            ->with('helpers.dictionary.converter.synonyms.de.target')
            ->will($this->returnValue($outputFile));

        $this->subject->openthesaurusToSynonyms($inputFile, 'de');

        $this->assertSame(
            file_get_contents($outputFile),
            file_get_contents($expectedOutputFile),
            'Generated Output for openthesaurus was not as expected.'
        );
    }
}
