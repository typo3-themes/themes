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

use RuntimeException;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Class TsParserUtility.
 *
 * Provides an API to the complex TSParser
 */
class TsParserUtility implements SingletonInterface
{
    /**
     * @var ExtendedTemplateService
     */
    protected ExtendedTemplateService $tsParser;

    protected array $tsParserTplRow = [];

    protected array $tsParserConstants = [];

    protected bool $tsParserInitialized = false;

    /**
     * @param $pid
     */
    public function applyToPid($pid, array $constants, array $isSetConstants = [])
    {
        $this->initializeTsParser($pid);
        $this->setConstants($pid, $constants, $isSetConstants);
        //@todo add hook to apply additional options
    }

    /**
     * @param $pageId
     *
     * @return bool
     */
    protected function initializeTsParser($pageId, int $templateUid = 0): bool
    {
        if (!$this->tsParserInitialized) {
            $this->tsParserInitialized = true;
            $this->tsParser = GeneralUtility::makeInstance('TYPO3\\CMS\Core\\TypoScript\\ExtendedTemplateService');
            // Do not log time-performance information
            $this->tsParser->tt_track = 0;

            $this->tsParser->ext_localGfxPrefix = ExtensionManagementUtility::extPath('tstemplate');
            $this->tsParser->ext_localWebGfxPrefix = PathUtility::stripPathSitePrefix(
                ExtensionManagementUtility::extPath('tstemplate')
            );

            $this->tsParserTplRow = $this->tsParser->ext_getFirstTemplate($pageId, $templateUid);

            if (!empty($this->tsParserTplRow)) {
                $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageId);
                try {
                    $rootLine = $rootlineUtility->get();
                } catch (RuntimeException) {
                    return false;
                }
                // This generates the constants/config + hierarchy info for the template.
                $this->tsParser->runThroughTemplates($rootLine, $templateUid);
                // The editable constants are returned in an array.
                $this->tsParserConstants = $this->tsParser->generateConfig_constants();
                // The returned constants are sorted in categories, that goes into the $tmpl->categories array
                $this->tsParser->ext_categorizeEditableConstants($this->tsParserConstants);
                $this->tsParser->ext_regObjectPositions($this->tsParserTplRow['constants']);
                // This array will contain key=[expanded constantname], value=linenumber in template. (after edit_divider, if any)
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * @param $pid
     * @param $constants
     *
     * @todo access check!
     */
    protected function setConstants($pid, $constants, array $isSetConstants = [])
    {
        $this->getConstants($pid);

        $postData = [
                'data' => $constants,
                'check' => $isSetConstants,
        ];

        $this->tsParser->changed = 0;
        //$this->tsParser->ext_dontCheckIssetValues = 1;
        $this->tsParser->ext_procesInput($postData, $_FILES, $this->tsParserConstants, $this->tsParserTplRow);

        if ($this->tsParser->changed) {
            // Set the data to be saved
            $saveId = $this->tsParserTplRow['uid'];
            $recData = [];
            $recData['sys_template'][$saveId]['constants'] = implode(chr(10), $this->tsParser->raw);
            // Create new  tce-object
            /**
             * @var DataHandler $tce
             */
            $tce = GeneralUtility::makeInstance(DataHandler::class);

            /*
             * Save data and clear the cache
             * (note: currently only admin-users can clear the cache)
             */
            $user = clone $GLOBALS['BE_USER'];
            $user->user['admin'] = 1;
            $tce->start($recData, [], $user);
            $tce->admin = 1;
            $tce->process_datamap();
            $tce->clear_cacheCmd('pages');
            unset($user);
        }
    }

    /**
     * @param $pid
     *
     * @return array|void
     */
    public function getConstants($pid)
    {
        $this->initializeTsParser($pid);

        $return = $this->tsParserConstants;
        if (!empty($return)) {
            foreach ($return as $key => $field) {
                $return[$key]['isDefault'] = ($field['value'] === $field['default_value']);

                if ($field['type'] === 'int+') {
                    $return[$key]['typeCleaned'] = 'Int';
                } elseif (str_starts_with((string) $field['type'], 'int')) {
                    $return[$key]['typeCleaned'] = 'Int';
                    $return[$key]['range'] = substr((string) $field['type'], 3);
                } elseif ($field['type'] === 'small') {
                    $return[$key]['typeCleaned'] = 'Text';
                } elseif ($field['type'] === 'color') {
                    $return[$key]['typeCleaned'] = 'Color';
                } elseif ($field['type'] === 'boolean') {
                    $return[$key]['typeCleaned'] = 'Boolean';
                } elseif ($field['type'] === 'string') {
                    $return[$key]['typeCleaned'] = 'String';
                } elseif (str_starts_with((string) $field['type'], 'file')) {
                    $return[$key]['typeCleaned'] = 'File';
                } elseif (str_starts_with((string) $field['type'], 'options')) {
                    $return[$key]['typeCleaned'] = 'Options';
                    $options = explode(',', substr((string) $field['type'], 8, -1));
                    $return[$key]['options'] = [];
                    foreach ($options as $option) {
                        $t = explode('=', $option);
                        if (count($t) === 2) {
                            $return[$key]['options'][$t[1]] = $t[0];
                        } else {
                            $return[$key]['options'][$t[0]] = $t[0];
                        }
                    }
                } elseif ($field['type'] === '') {
                    $return[$key]['typeCleaned'] = 'String';
                } else {
                    $return[$key]['typeCleaned'] = 'Fallback';
                }
            }

            return $return;
        }
    }

    /**
     * @param $pid
     *
     * @return array
     */
    public function getCategories($pid, array $categoriesToShow = [], array $deniedFields = []): array
    {
        $categories = [];

        $this->initializeTsParser($pid);
        $constants = $this->tsParser->generateConfig_constants();

        if (!empty($constants)) {
            foreach ($constants as $constantName => $constantArray) {
                if (!isset($constantArray['cat'])) {
                    continue;
                }
                $assignedCategories = GeneralUtility::trimExplode(',', $constantArray['cat'], true);
                foreach ($assignedCategories as $assignedCategory) {
                    if (!isset($categories[$assignedCategory])) {
                        $categories[$assignedCategory] = [];
                    }
                    $categories[$assignedCategory][$constantName] = $constantArray;
                }
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
     * @param $pid
     *
     * @return ExtendedTemplateService
     */
    protected function getTsParser($pid): ExtendedTemplateService
    {
        $this->initializeTsParser($pid);

        return $this->tsParser;
    }
}
