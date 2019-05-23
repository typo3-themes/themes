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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TemplateModuleBodyPostProcessHook.
 *
 * Hook to change the look of the template module
 *
 * @todo get it working again, is broken since 6.2
 */
class TemplateModuleBodyPostProcessHook
{
    /**
     * $params = array(
     *     'moduleTemplateFilename' => &$this->moduleTemplateFilename,
     *     'moduleTemplate' => &$this->moduleTemplate,
     *     'moduleBody' => &$moduleBody,
     *     'markers' => &$markerArray,
     *     'parentObject' => &$this
     * );.
     *
     * @param $params
     * @param $pObj
     *
     * @return void
     */
    public function main(&$params, &$pObj)
    {
        /*
         * @var $repository \KayStrobach\Themes\Domain\Repository\ThemeRepository
         * @var $view \TYPO3\CMS\Fluid\View\StandaloneView
         */

        $getSet = GeneralUtility::_GP('SET');

        if (($getSet['function'] === 'tx_tstemplateinfo' || !$getSet['function'])
            && ($params['moduleTemplateFilename'] === 'templates/tstemplate.html')) {
            $repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

            $view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

            $view->setFormat('html');
            $view->setTemplatePathAndFilename(
                GeneralUtility::getFileAbsFileName('EXT:themes/Resources/Private/Templates/TsTemplateThemeData.html')
            );
            $view->assignMultiple([
                'selectedTheme'    => $repository->findByPageId(GeneralUtility::_GP('id')),
                'selectableThemes' => $repository->findAll(),
            ]);

            $params['markers']['CONTENT'] = str_replace('<table class="t3-table-info">', '<table class="t3-table-info">'.$view->render(), $params['markers']['CONTENT']);

            return;
        }
    }
}
