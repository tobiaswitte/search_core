<?php
namespace Codappix\SearchCore\Domain\Index\TcaIndexer;

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

use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use Codappix\SearchCore\Configuration\InvalidArgumentException as InvalidConfigurationArgumentException;
use Codappix\SearchCore\DataProcessing\ProcessorInterface;
use Codappix\SearchCore\Domain\Index\IndexingException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Encapsulate logik related to TCA configuration.
 */
class TcaTableService
{
    /**
     * TCA for current table.
     * !REFERENCE! To save memory.
     * @var array
     */
    protected $tca;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var ConfigurationContainerInterface
     */
    protected $configuration;

    /**
     * @var RelationResolver
     */
    protected $relationResolver;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Inject log manager to get concrete logger from it.
     *
     * @param \TYPO3\CMS\Core\Log\LogManager $logManager
     */
    public function injectLogger(\TYPO3\CMS\Core\Log\LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $tableName
     * @param ConfigurationContainerInterface $configuration
     */
    public function __construct(
        $tableName,
        RelationResolver $relationResolver,
        ConfigurationContainerInterface $configuration
    ) {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new IndexingException(
                'Table "' . $tableName . '" is not configured in TCA.',
                IndexingException::CODE_UNKOWN_TCA_TABLE
            );
        }

        $this->tableName = $tableName;
        $this->tca = &$GLOBALS['TCA'][$this->tableName];
        $this->configuration = $configuration;
        $this->relationResolver = $relationResolver;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getTableClause()
    {
        if ($this->tableName === 'pages') {
            return $this->tableName;
        }

        return $this->tableName . ' LEFT JOIN pages on ' . $this->tableName . '.pid = pages.uid';
    }

    /**
     * Filter the given records by root line blacklist settings.
     *
     * @param array &$records
     * @return void
     */
    public function filterRecordsByRootLineBlacklist(array &$records)
    {
        $records = array_filter(
            $records,
            function ($record) {
                return ! $this->isRecordBlacklistedByRootline($record);
            }
        );
    }

    /**
     * @param array &$record
     */
    public function prepareRecord(array &$record)
    {
        $this->relationResolver->resolveRelationsForRecord($this, $record);

        try {
            foreach ($this->configuration->get('indexing.' . $this->tableName . '.dataProcessing') as $configuration) {
                $dataProcessor = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($configuration['_typoScriptNodeValue']);
                if ($dataProcessor instanceof ProcessorInterface) {
                    $record = $dataProcessor->processRecord($record, $configuration);
                }
            }
        } catch (InvalidConfigurationArgumentException $e) {
            // Nothing to do.
        }

        if (isset($record['uid']) && !isset($record['search_identifier'])) {
            $record['search_identifier'] = $record['uid'];
        }
        if (isset($record[$this->tca['ctrl']['label']]) && !isset($record['search_title'])) {
            $record['search_title'] = $record[$this->tca['ctrl']['label']];
        }
    }

    /**
     * @return string
     */
    public function getWhereClause()
    {
        $whereClause = '1=1'
            . BackendUtility::BEenableFields($this->tableName)
            . BackendUtility::deleteClause($this->tableName)
            . ' AND pages.no_search = 0'
            ;

        if ($this->tableName !== 'pages') {
            $whereClause .= BackendUtility::BEenableFields('pages')
                . BackendUtility::deleteClause('pages')
            ;
        }

        $userDefinedWhere = $this->configuration->getIfExists('indexing.' . $this->getTableName() . '.additionalWhereClause');
        if (is_string($userDefinedWhere)) {
            $whereClause .= ' AND ' . $userDefinedWhere;
        }

        if ($this->isBlacklistedRootLineConfigured()) {
            $whereClause .= ' AND pages.uid NOT IN ('
                . implode(',', $this->getBlacklistedRootLine())
                . ')'
                . ' AND pages.pid NOT IN ('
                . implode(',', $this->getBlacklistedRootLine())
                . ')';
        }

        $this->logger->debug('Generated where clause.', [$this->tableName, $whereClause]);
        return $whereClause;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        $fields = array_merge(
            ['uid','pid'],
            array_filter(
                array_keys($this->tca['columns']),
                function ($columnName) {
                    return !$this->isSystemField($columnName)
                        && !$this->isUserField($columnName)
                        && !$this->isPassthroughField($columnName)
                        ;
                }
            )
        );

        foreach ($fields as $key => $field) {
            $fields[$key] = $this->tableName . '.' . $field;
        }

        $this->logger->debug('Generated fields.', [$this->tableName, $fields]);
        return implode(',', $fields);
    }

    /**
     * @param string
     * @return bool
     */
    protected function isSystemField($columnName)
    {
        $systemFields = [
            // Versioning fields,
            // https://docs.typo3.org/typo3cms/TCAReference/Reference/Ctrl/Index.html#versioningws
            't3ver_oid', 't3ver_id', 't3ver_label', 't3ver_wsid',
            't3ver_state', 't3ver_stage', 't3ver_count', 't3ver_tstamp',
            't3ver_move_id', 't3ver_swapmode',
            $this->tca['ctrl']['transOrigDiffSourceField'],
            $this->tca['ctrl']['cruser_id'],
            $this->tca['ctrl']['fe_cruser_id'],
            $this->tca['ctrl']['fe_crgroup_id'],
            $this->tca['ctrl']['languageField'],
            $this->tca['ctrl']['origUid'],
        ];

        return in_array($columnName, $systemFields);
    }

    protected function isUserField(string $columnName) : bool
    {
        $config = $this->getColumnConfig($columnName);
        return isset($config['type']) && $config['type'] === 'user';
    }

    protected function isPassthroughField(string $columnName) : bool
    {
        $config = $this->getColumnConfig($columnName);
        return isset($config['type']) && $config['type'] === 'passthrough';
    }

    /**
     * @param string $columnName
     * @return array
     * @throws InvalidArgumentException
     */
    public function getColumnConfig($columnName)
    {
        if (!isset($this->tca['columns'][$columnName])) {
            throw new InvalidArgumentException(
                'Column does not exist.',
                InvalidArgumentException::COLUMN_DOES_NOT_EXIST
            );
        }

        return $this->tca['columns'][$columnName]['config'];
    }

    /**
     * Checks whether the given record was blacklisted by root line.
     * This can be configured by typoscript as whole root lines can be black listed.
     *
     * Also further TYPO3 mechanics are taken into account. Does a valid root
     * line exist, is page inside a recycler, is inherited start- endtime
     * excluded, etc.
     *
     * @param array &$record
     * @return bool
     */
    protected function isRecordBlacklistedByRootline(array &$record)
    {
        $pageUid = $record['pid'];
        if ($this->tableName === 'pages') {
            $pageUid = $record['uid'];
        }

        try {
            $rootline = $this->objectManager->get(RootlineUtility::class, $pageUid)->get();
        } catch (\RuntimeException $e) {
            $this->logger->notice(
                sprintf('Could not fetch rootline for page %u, because: %s', $pageUid, $e->getMessage()),
                [$record, $e]
            );
            return true;
        }

        foreach ($rootline as $pageInRootLine) {
            // Check configured black list if present.
            if ($this->isBlackListedRootLineConfigured()
                && in_array($pageInRootLine['uid'], $this->getBlackListedRootLine())
            ) {
                $this->logger->info(
                    sprintf(
                        'Record %u is black listed due to configured root line configuration of page %u.',
                        $record['uid'],
                        $pageInRootLine['uid']
                    ),
                    [$record, $pageInRootLine]
                );
                return true;
            }

            if ($pageInRootLine['extendToSubpages'] && (
                ($pageInRootLine['endtime'] > 0 && $pageInRootLine['endtime'] <= time())
                || ($pageInRootLine['starttime'] > 0 && $pageInRootLine['starttime'] >= time())
            )) {
                $this->logger->info(
                    sprintf(
                        'Record %u is black listed due to configured timing of parent page %u.',
                        $record['uid'],
                        $pageInRootLine['uid']
                    ),
                    [$record, $pageInRootLine]
                );
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether any page uids are black listed.
     *
     * @return bool
     */
    protected function isBlackListedRootLineConfigured()
    {
        return (bool) $this->configuration->getIfExists('indexing.' . $this->getTableName() . '.rootLineBlacklist');
    }

    /**
     * Get the list of black listed root line page uids.
     *
     * @return array<Int>
     */
    protected function getBlackListedRootLine()
    {
        return GeneralUtility::intExplode(',', $this->configuration->getIfExists('indexing.' . $this->getTableName() . '.rootLineBlacklist'));
    }
}
