<?php
namespace Codappix\SearchCore\Tests\DataProcessing;

/*
 * Copyright (C) 2016  Daniel Siepmann <coding@daniel-siepmann.de>
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

use Codappix\SearchCore\DataProcessing\RelationResolverProcessor;
use Codappix\SearchCore\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class RelationResolverProcessorTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function resolveInlineRelation()
    {
        $this->importDataSet('Tests/Functional/Fixtures/Indexing/TcaIndexer/RelationResolver/InlineRelation.xml');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $table = 'sys_file';

        // Only by adding the field to showitem, it will be processed by FormEngine.
        // We use this field to test inline relations, as there is only one alternative.
        $GLOBALS['TCA']['sys_file']['types'][1]['showitem'] .= ',metadata';

        $subject = $objectManager->get(RelationResolverProcessor::class);
        $record = BackendUtility::getRecord($table, 1);
        $record = $subject->processRecord($record, ['tableName' => $table]);

        $this->assertEquals(
            [
                'title of file',
                'title of file',
            ],
            $record['metadata'],
            'Inline relation was not resolved as expected.'
        );
    }

    /**
     * @test
     */
    public function resolveStaticSelectItems()
    {
        $this->importDataSet('Tests/Functional/Fixtures/Indexing/TcaIndexer/RelationResolver/StaticSelectItems.xml');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $table = 'tt_content';

        $subject = $objectManager->get(RelationResolverProcessor::class);
        $record = BackendUtility::getRecord($table, 1);
        $record = $subject->processRecord($record, ['tableName' => $table]);

        $this->assertEquals(
            'Insert Plugin',
            $record['CType'],
            'Static select item was not resolved as expected.'
        );
    }

    /**
     * @test
     */
    public function resolveForeignDb()
    {
        $this->importDataSet('Tests/Functional/Fixtures/Indexing/TcaIndexer/RelationResolver/ForeignDb.xml');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $table = 'tt_content';

        $subject = $objectManager->get(RelationResolverProcessor::class);
        $record = BackendUtility::getRecord($table, 1);
        $record = $subject->processRecord($record, ['tableName' => $table]);

        $this->assertEquals(
            [
                'Record 2',
                'Record 3',
            ],
            $record['records'],
            'Foreign db relation was not resolved as expected.'
        );
    }

    /**
     * @test
     */
    public function resolveForeignMmSelect()
    {
        $this->importDataSet('Tests/Functional/Fixtures/Indexing/TcaIndexer/RelationResolver/ForeignMmSelect.xml');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $table = 'tt_content';

        $subject = $objectManager->get(RelationResolverProcessor::class);
        $record = BackendUtility::getRecord($table, 1);
        $record = $subject->processRecord($record, ['tableName' => $table]);

        $this->assertEquals(
            [
                'Category 1',
                'Category 2',
            ],
            $record['categories'],
            'Foreign mm select relation was not resolved as expected.'
        );
    }
}
