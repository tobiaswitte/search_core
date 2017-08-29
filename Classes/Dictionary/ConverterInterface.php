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

/**
 * Converter are used to convert one format into another.
 * E.g. convert a thesaurus file into usable synonyms for search service.
 */
interface ConverterInterface
{
    /**
     * Convert a valid openthesaurus file to a file containing synonyms, ready
     * to use with TypoScript options.
     *
     * Returns absolute path to generated file.
     *
     * @param string $openthesaurusToSynonyms Path to openthesaurus .txt-file.
     * @param string $language The language to use, e.g. to fetch configuration.
     *
     * @return string
     */
    public function openthesaurusToSynonyms($openthesaurusFilePath, $language);
}
