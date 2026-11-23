<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Utilities;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\AST\Traverser\AstTraverser;
use TYPO3\CMS\Core\TypoScript\AST\Visitor\AstConstantCommentVisitor;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateTreeBuilder;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Traverser\IncludeTreeTraverser;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Visitor\IncludeTreeCommentAwareAstBuilderVisitor;
use TYPO3\CMS\Core\TypoScript\Tokenizer\LosslessTokenizer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Class TsParserUtility.
 *
 * Reads and writes the editable constants ("# cat=…; type=…; label=…") of the first template of a page,
 * like the constant editor of EXT:tstemplate. Uses the TypoScript parser of TYPO3 v12 and later,
 * the ExtendedTemplateService of older versions is gone.
 */
class TsParserUtility implements SingletonInterface
{
    /**
     * The first template record of the page, the constants are written into it.
     */
    protected array $tsParserTplRow = [];

    /**
     * Editable constants by name, see AstConstantCommentVisitor::getConstants().
     */
    protected array $tsParserConstants = [];

    protected ?int $initializedForPage = null;

    public function applyToPid($pid, array $constants, array $isSetConstants = [])
    {
        $this->initializeTsParser((int)$pid);
        $this->setConstants((int)$pid, $constants, $isSetConstants);
        //@todo add hook to apply additional options
    }

    protected function initializeTsParser(int $pageId): bool
    {
        if ($this->initializedForPage === $pageId) {
            return $this->tsParserTplRow !== [];
        }
        $this->initializedForPage = $pageId;
        $this->tsParserTplRow = $this->findFirstTemplateOnPage($pageId);
        $this->tsParserConstants = [];
        if ($this->tsParserTplRow === []) {
            return false;
        }
        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageId)->get();
        } catch (\RuntimeException) {
            return false;
        }

        $request = ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface ? $GLOBALS['TYPO3_REQUEST'] : null;
        $sysTemplateRows = GeneralUtility::makeInstance(SysTemplateRepository::class)
            ->getSysTemplateRowsByRootlineWithUidOverride($rootLine, $request, (int)$this->tsParserTplRow['uid']);
        $tokenizer = GeneralUtility::makeInstance(LosslessTokenizer::class);
        $constantIncludeTree = GeneralUtility::makeInstance(SysTemplateTreeBuilder::class)
            ->getTreeBySysTemplateRowsAndSite('constants', $sysTemplateRows, $tokenizer, $this->findSite($pageId));
        $astBuilderVisitor = GeneralUtility::makeInstance(IncludeTreeCommentAwareAstBuilderVisitor::class);
        GeneralUtility::makeInstance(IncludeTreeTraverser::class)->traverse($constantIncludeTree, [$astBuilderVisitor]);
        $commentVisitor = GeneralUtility::makeInstance(AstConstantCommentVisitor::class);
        GeneralUtility::makeInstance(AstTraverser::class)->traverse($astBuilderVisitor->getAst(), [$commentVisitor]);
        $this->tsParserConstants = $commentVisitor->getConstants();
        return true;
    }

    /**
     * @todo access check!
     */
    protected function setConstants(int $pid, array $constants, array $isSetConstants = [])
    {
        if ($this->tsParserTplRow === []) {
            return;
        }
        $updatedConstants = $this->updateTemplateConstants($constants, $isSetConstants, (string)($this->tsParserTplRow['constants'] ?? ''));
        if ($updatedConstants === null) {
            return;
        }
        $saveId = empty($this->tsParserTplRow['_ORIG_uid']) ? $this->tsParserTplRow['uid'] : $this->tsParserTplRow['_ORIG_uid'];
        $recData = [];
        $recData['sys_template'][$saveId]['constants'] = implode(chr(10), $updatedConstants);
        /** @var DataHandler $tce */
        $tce = GeneralUtility::makeInstance(DataHandler::class);
        // Save data and clear the cache (only admin users can clear the cache)
        $user = clone $GLOBALS['BE_USER'];
        $user->user['admin'] = 1;
        $tce->start($recData, [], $user);
        $tce->process_datamap();
        $tce->clear_cacheCmd('pages');
        unset($user);
        // read the constants again next time
        $this->initializedForPage = null;
    }

    /**
     * @return array|void
     */
    public function getConstants($pid)
    {
        $this->initializeTsParser((int)$pid);

        $return = $this->tsParserConstants;
        if (!empty($return)) {
            foreach ($return as $key => $field) {
                $type = (string)($field['type'] ?? '');
                $return[$key]['isDefault'] = ((string)($field['value'] ?? '') === (string)($field['default_value'] ?? ''));

                if ($type === 'int+' || $type === 'int') {
                    $return[$key]['typeCleaned'] = 'Int';
                    if (isset($field['typeIntMin']) || isset($field['typeIntMax'])) {
                        $return[$key]['range'] = '[' . ($field['typeIntMin'] ?? '') . '-' . ($field['typeIntMax'] ?? '') . ']';
                    }
                } elseif ($type === 'color') {
                    $return[$key]['typeCleaned'] = 'Color';
                } elseif ($type === 'boolean') {
                    $return[$key]['typeCleaned'] = 'Boolean';
                } elseif ($type === 'string' || $type === '') {
                    $return[$key]['typeCleaned'] = 'String';
                } elseif ($type === 'options') {
                    $return[$key]['typeCleaned'] = 'Options';
                    $return[$key]['options'] = [];
                    foreach ($field['labelValueArray'] ?? [] as $option) {
                        $return[$key]['options'][$option['value']] = $option['label'];
                    }
                } else {
                    $return[$key]['typeCleaned'] = 'Fallback';
                }
            }

            return $return;
        }
    }

    public function getCategories($pid, array $categoriesToShow = [], array $deniedFields = []): array
    {
        $categories = [];

        $this->initializeTsParser((int)$pid);
        foreach ($this->tsParserConstants as $constantName => $constantArray) {
            if (!isset($constantArray['cat'])) {
                continue;
            }
            $assignedCategories = GeneralUtility::trimExplode(',', (string)$constantArray['cat'], true);
            foreach ($assignedCategories as $assignedCategory) {
                $categories[$assignedCategory][$constantName] = $constantArray;
            }
        }

        foreach ($categories as $categoryName => $category) {
            if ((count($categoriesToShow) === 0) || (in_array($categoryName, $categoriesToShow))) {
                foreach (array_keys($category) as $constantName) {
                    if (in_array($constantName, $deniedFields)) {
                        unset($categories[$categoryName][$constantName]);
                    }
                }
            } else {
                unset($categories[$categoryName]);
            }
        }

        return $categories;
    }

    /**
     * The first template record on the page (like the constant editor of the core without selection).
     */
    protected function findFirstTemplateOnPage(int $pageId): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_template');
        $row = $queryBuilder
            ->select('*')
            ->from('sys_template')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageId, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)))
            ->orderBy('sorting')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        return $row === false ? [] : $row;
    }

    protected function findSite(int $pageId): ?SiteInterface
    {
        try {
            return GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
        } catch (SiteNotFoundException) {
            return null;
        }
    }

    /**
     * Like the constant editor of the core: changes the lines of the template constants,
     * "check[name] = checked" keeps a value in the template, without it the value is removed.
     *
     * @return list<string>|null the new lines, null if nothing changed
     */
    protected function updateTemplateConstants(array $data, array $check, string $rawTemplateConstants): ?array
    {
        $rawTemplateConstantsArray = explode(chr(10), $rawTemplateConstants);
        $constantPositions = $this->calculateConstantPositions($rawTemplateConstantsArray);
        $valuesHaveChanged = false;
        foreach ($data as $key => $value) {
            if (!isset($this->tsParserConstants[$key])) {
                // Ignore if there is no constant definition for this constant key
                continue;
            }
            if (!isset($check[$key]) || ($check[$key] !== 'checked' && isset($constantPositions[$key]))) {
                // Remove value if the checkbox is not set, indicating "value to be dropped from template"
                if (isset($constantPositions[$key])) {
                    unset($rawTemplateConstantsArray[$constantPositions[$key]]);
                    $valuesHaveChanged = true;
                }
                continue;
            }
            if ($check[$key] !== 'checked') {
                continue;
            }
            $definition = $this->tsParserConstants[$key];
            switch ($definition['type'] ?? '') {
                case 'int':
                    $value = (string)MathUtility::forceIntegerInRange((int)$value, (int)($definition['typeIntMin'] ?? PHP_INT_MIN), (int)($definition['typeIntMax'] ?? PHP_INT_MAX));
                    break;
                case 'int+':
                    $value = (string)MathUtility::forceIntegerInRange((int)$value, (int)($definition['typeIntMin'] ?? 0), (int)($definition['typeIntMax'] ?? PHP_INT_MAX));
                    break;
                case 'boolean':
                    if ($value) {
                        $value = ($definition['trueValue'] ?? false) ?: '1';
                    }
                    break;
            }
            $value = is_array($value) ? implode(',', $value) : (string)$value;
            if ((string)($definition['value'] ?? '') !== $value || !isset($constantPositions[$key])) {
                $theValue = ' ' . trim($value);
                if (isset($constantPositions[$key])) {
                    $parts = explode('=', $rawTemplateConstantsArray[$constantPositions[$key]], 2);
                    if (count($parts) === 2) {
                        $parts[1] = $theValue;
                    }
                    $rawTemplateConstantsArray[$constantPositions[$key]] = implode('=', $parts);
                } else {
                    $rawTemplateConstantsArray[] = $key . ' =' . $theValue;
                }
                $valuesHaveChanged = true;
            }
        }
        return $valuesHaveChanged ? array_values($rawTemplateConstantsArray) : null;
    }

    /**
     * @return array<string, int> line of each constant assignment in the template constants
     */
    protected function calculateConstantPositions(
        array $rawTemplateConstantsArray,
        array &$constantPositions = [],
        string $prefix = '',
        int $braceLevel = 0,
        int &$lineCounter = 0
    ): array {
        while (isset($rawTemplateConstantsArray[$lineCounter])) {
            $line = ltrim($rawTemplateConstantsArray[$lineCounter]);
            $lineCounter++;
            if (!$line || $line[0] === '[') {
                // Ignore empty lines and conditions
                continue;
            }
            if (strcspn($line, '}#/') !== 0) {
                $operatorPosition = strcspn($line, ' {=<');
                $key = substr($line, 0, $operatorPosition);
                $line = ltrim(substr($line, $operatorPosition));
                if ($line !== '' && $line[0] === '=') {
                    $constantPositions[$prefix . $key] = $lineCounter - 1;
                } elseif ($line !== '' && $line[0] === '{') {
                    $braceLevel++;
                    $this->calculateConstantPositions($rawTemplateConstantsArray, $constantPositions, $prefix . $key . '.', $braceLevel, $lineCounter);
                }
            } elseif ($line[0] === '}') {
                $braceLevel--;
                if ($braceLevel < 0) {
                    $braceLevel = 0;
                } else {
                    break;
                }
            }
        }
        return $constantPositions;
    }
}
