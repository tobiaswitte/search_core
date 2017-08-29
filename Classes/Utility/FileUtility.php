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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileUtility implements FileUtilityInterface
{
    public function processFile($originalFile, \SplFileObject $targetFile, $callbackFilter, $callbackProcessing)
    {
        foreach ($this->getFileContents($originalFile) as $line) {
            if ($callbackFilter($line)) {
                continue;
            }

            $returnValue = $callbackProcessing($line);
            if (is_string($returnValue)) {
                $targetFile->fwrite($returnValue);
            }
        }
    }

    public function getFile($filePath, $mode = 'r')
    {
        $filePath = GeneralUtility::getFileAbsFileName($filePath, false);

        if (!is_file($filePath) && $mode[0] !== 'r') {
            GeneralUtility::mkdir_deep(dirname($filePath));
        }

        return new \SplFileObject($filePath, $mode);
    }

    protected function getFileContents($filePath)
    {
        $file = $this->getFile($filePath);
        while (!$file->eof()) {
            yield $file->fgets();
        }
    }
}
