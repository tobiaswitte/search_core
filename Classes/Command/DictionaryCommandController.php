<?php
namespace Codappix\SearchCore\Command;

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

use Codappix\SearchCore\Dictionary\ConverterInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Interacts with dictionaries, e.g. convert foreign files to be used with
 * search services.
 *
 * Works as a proxy between Converter and CLI.
 */
class DictionaryCommandController extends CommandController
{
    /**
     * @var ConverterInterface
     */
    protected $dictionaryConverter;

    public function __construct(ConverterInterface $dictionaryConverter)
    {
        $this->dictionaryConverter = $dictionaryConverter;
    }

    /**
     * Will convert a plain text file from Openthesaurus, to a ready to use file for elasticsearch synonym filter configuration.
     *
     * @param string $openthesaurusFile
     * @param string $language The language to use, e.g. 'de'
     */
    public function openthesaurusToSynonymsCommand($openthesaurusFile, $language = 'de')
    {
        $this->dictionaryConverter->openthesaurusToSynonyms($openthesaurusFile, $language);
    }
}
