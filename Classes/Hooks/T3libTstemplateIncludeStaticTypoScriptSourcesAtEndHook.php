<?php

namespace KayStrobach\Themes\Hooks;

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

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Install\Configuration\Exception;

/**
 * Class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.
 *
 * Hook to include the TypoScript during the rendering
 */
class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook
{
    /**
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array $params array of parameters from the parent class. Includes idList, templateId, pid, and row.
     * @param TemplateService $pObj Reference back to parent object, t3lib_tstemplate or one of its subclasses.
     *
     * @return void
     * @throws Exception
     */
    public static function main(&$params, TemplateService &$pObj)
    {
        $idList = $params['idList'];
        $templateId = $params['templateId'];
        $pid = $params['pid'];
        if ($templateId === $idList) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_template');
            $queryBuilder->select('*')
                ->from('sys_template')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter((int)$pid, \PDO::PARAM_INT)
                    )
                );
            /** @var  \Doctrine\DBAL\Driver\Statement $statement */
            $statement = $queryBuilder->execute();
            if ($statement->rowCount()>0) {
                $tRow = $statement->fetch();
                $themeIdentifier = $tRow['tx_themes_skin'];
                //
                // Call hook for possible manipulation of current skin.
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'])) {
                    $tempParamsForHook = ['theme' => $themeIdentifier];
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'] as $userFunc) {
                        $themeIdentifier = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $ref = null);
                    }
                }
                if (empty($themeIdentifier)) {
                    $themeIdentifier = $tRow['tx_themes_skin'];
                }
                /** @var ThemeRepository $themeRepository */
                $themeRepository = GeneralUtility::makeInstance(ThemeRepository::class);
                /** @var Theme $theme */
                $theme = $themeRepository->findByUid($themeIdentifier);
                if ($theme === null) {
                    // fallback if the hook returns a undefined theme
                    $theme = $themeRepository->findByUid($tRow['tx_themes_skin']);
                }
                if ($theme !== null) {
                    $themeExtensions = GeneralUtility::trimExplode(',', $tRow['tx_themes_extensions'], true);
                    $themeFeatures = GeneralUtility::trimExplode(',', $tRow['tx_themes_features'], true);
                    $theme->addTypoScriptForFe($params, $pObj, $themeExtensions, $themeFeatures);
                }
                // @todo add hook to inject template overlays, e.g. for previewed constants before save ...
                // Call hook for possible manipulation of current skin. constants
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'])) {
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'] as $userFunc) {
                        $themeItem = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $pObj);
                        $pObj->processTemplate(
                            $themeItem,
                            $params['idList'].',themes_modifyTsOverlay',
                            $params['pid'],
                            'themes_themes_modifyTsOverlay',
                            $params['templateId']
                        );
                    }
                }
            }
        }
    }
}
