<?php
namespace Codappix\SearchCore\Dictionary;

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
use Codappix\SearchCore\Utility\FileUtilityInterface;

class Converter implements ConverterInterface
{
    /**
     * @var FileUtilityInterface
     */
    protected $fileUtility;

    /**
     * @var ConfigurationContainerInterface
     */
    protected $configuration;

    public function __construct(FileUtilityInterface $fileUtility, ConfigurationContainerInterface $configuration)
    {
        $this->fileUtility = $fileUtility;
        $this->configuration = $configuration;
    }

    public function openthesaurusToSynonyms($openthesaurusFilePath, $language)
    {
        $this->fileUtility->processFile(
            $openthesaurusFilePath,
            $this->fileUtility->getFile(
                $this->configuration->get('helpers.dictionary.converter.synonyms.' . $language . '.target'),
                'w+'
            ),
            function ($line) {
                return $line[0] === '#';
            },
            function ($line) {
                $synonyms = explode(';', mb_strtolower(trim($line)));
                $synonyms = array_unique($synonyms);
                // Filter stuff like slang
                $synonyms = array_filter($synonyms, function ($synonym) {
                    return substr($synonym, -1) !== ')';
                });

                if (count($synonyms) < 2) {
                    return false;
                }
                return implode(',', $synonyms). PHP_EOL;
            }
        );
    }
}
