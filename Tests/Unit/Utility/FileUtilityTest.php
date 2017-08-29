<?php
namespace Codappix\SearchCore\Tests\Unit\Utility;

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

use Codappix\SearchCore\Tests\Unit\AbstractUnitTestCase;
use Codappix\SearchCore\Utility\FileUtility;

class FileUtilityTest extends AbstractUnitTestCase
{
    /**
     * @var FileUtility
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new FileUtility();
    }

    public function tearDown()
    {
        $this->rrmdir($this->getFilesystemPathForTests());
    }

    /**
     * @test
     * @dataProvider creationModes
     */
    public function recursiveFilePathGetsCreatedForAllowedModes($mode)
    {
        $filePath = implode(DIRECTORY_SEPARATOR, [
            $this->getFilesystemPathForTests(), 'Subfolder1', 'Subfolder2', 'Examplefile.txt',
        ]);

        $folderPath = dirname($filePath);
        $this->assertFalse(file_exists($folderPath), 'Folder exists before creation.');

        $this->subject->getFile($filePath, $mode);

        $this->assertTrue(file_exists($folderPath), 'Folder was not created.');
    }

    /**
     * @test
     * @dataProvider noneCreationModes
     */
    public function recursiveFilePathGetsNotCreatedForUnallowedModes($mode)
    {
        $filePath = implode(DIRECTORY_SEPARATOR, [
            $this->getFilesystemPathForTests(), 'Subfolder1', 'Subfolder2', 'Examplefile.txt',
        ]);

        $folderPath = dirname($filePath);
        $this->assertFalse(file_exists($folderPath), 'Folder exists before calling subject.');

        $this->expectException(\RuntimeException::class);
        $this->subject->getFile($filePath, $mode);
    }

    public function creationModes()
    {
        return [
            ['w'],
            ['w+'],
            ['a'],
            ['a+'],
            ['x'],
            ['x+'],
            ['c'],
            ['c+'],
        ];
    }

    public function noneCreationModes()
    {
        return [
            ['r'],
            ['r+'],
        ];
    }

    /**
     * Mocking filesystem is not possible due to TYPO3 path checks.
     *
     * @return string
     */
    protected function getFilesystemPathForTests()
    {
        return implode(DIRECTORY_SEPARATOR, [ __DIR__, 'Filesystem']);
    }

    /**
     * Recursively remove folder.
     * Used to cleanup.
     *
     * @param string $dir
     */
    protected function rrmdir($dir) {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
                continue;
            }
            unlink($filename);
        }
    }
}
