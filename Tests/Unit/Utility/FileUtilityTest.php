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
use org\bovigo\vfs\vfsStream;

class FileUtilityTest extends AbstractUnitTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $fileSystem;

    public function setUp()
    {
        $this->fileSystem = vfsStream::setup('root');
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
        $subject = new FileUtility();
        $filePath = implode(DIRECTORY_SEPARATOR, [
            $this->getFilesystemPathForTests(), 'Subfolder1', 'Subfolder2', 'Examplefile.txt',
        ]);

        $folderPath = dirname($filePath);
        $this->assertFalse(file_exists($folderPath), 'Folder exists before creation.');

        $subject->getFile($filePath, $mode);

        $this->assertTrue(file_exists($folderPath), 'Folder was not created.');
    }

    /**
     * @test
     * @dataProvider noneCreationModes
     */
    public function recursiveFilePathGetsNotCreatedForUnallowedModes($mode)
    {
        $subject = new FileUtility();
        $filePath = implode(DIRECTORY_SEPARATOR, [
            $this->getFilesystemPathForTests(), 'Subfolder1', 'Subfolder2', 'Examplefile.txt',
        ]);

        $folderPath = dirname($filePath);
        $this->assertFalse(file_exists($folderPath), 'Folder exists before calling subject.');

        $this->expectException(\RuntimeException::class);
        $subject->getFile($filePath, $mode);
    }

    /**
     * @test
     */
    public function filteringFileProcessingWorks()
    {
        $inputFileName = 'root/input.txt';
        $outputFileName = 'root/output.txt';
        $originalContent = implode(PHP_EOL, ['# Skipped', 'Line 1', '# Skipped', 'Line 2', '# Skipped', 'Line 3']);
        $expectedContent = implode(PHP_EOL, ['Line 1', 'Line 3']);

        $inputFile = vfsStream::url($inputFileName);
        file_put_contents($inputFile, $originalContent);
        $inputFileObject = new \SplFileObject($inputFile);
        $subject = $this->getMockBuilder(FileUtility::class)
            ->setMethodsExcept(['processFile', 'getFileContents'])
            ->getMock();
        $subject->expects($this->once())
            ->method('getFile')
            ->with($this->equalTo($inputFile))
            ->will($this->returnValue($inputFileObject));

        $subject->processFile(
            vfsStream::url($inputFileName),
            new \SplFileObject(vfsStream::url($outputFileName), 'w+'),
            function ($line) {
                return $line[0] === '#';
            },
            function ($line) {
                if (strpos($line, 'Line 2') === 0) {
                    return false;
                }

                return $line;
            }
        );

        $this->assertSame(
            $expectedContent,
            file_get_contents(vfsStream::url($outputFileName)),
            'Lines were not filtered as expected.'
        );
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
