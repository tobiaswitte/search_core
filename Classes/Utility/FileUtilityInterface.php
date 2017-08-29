<?php
namespace Codappix\SearchCore\Utility;

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

interface FileUtilityInterface
{
    /**
     * Execute $callbackProcessing for each line in $originalFile and writes the result back to $targetFile.
     * Will skip processing for lines where $callbackFilter returns true.
     *
     * @param string $originalFile The file path to the file to process.
     * @param \SplFileObject $targetFile The file to write result to.
     * @param callable $callbackFilter Filter receiving a single line. On return true it will be skipped.
     * @param callable $callbackProcessing Recieves a single line and result is
     *        wrote back to $targetFile. If not a string is returned, it's not
     *        written to the file.
     *
     * @return void
     */
    public function processFile($originalFile, \SplFileObject $targetFile, $callbackFilter, $callbackProcessing);

    /**
     * Returns \SplFileObject representation of $filePath. Takes TYPO3 specifics
     * into account, e.g. "EXT:" resolution.
     *
     * @param string $filePath
     * @param string $mode See fopen
     *
     * @return \SplFileObject
     * @throws \RuntimeException If the filename cannot be opened.
     */
    public function getFile($filePath, $mode = 'r');
}
