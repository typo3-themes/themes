<?php

declare(strict_types=1);

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

use TYPO3\CMS\Core\Page\PageRenderer as PageRendererCore;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class/Function which adds the necessary ExtJS and pure JS stuff for themes.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class PageRenderer implements SingletonInterface
{
    /**
     * Wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess).
     *
     * @param array $parameters An array of available parameters
     * @param PageRendererCore $pageRenderer The parent object that triggered this hook
     */
    public function addJSCSS(array $parameters, PageRendererCore $pageRenderer)
    {
        if ($pageRenderer->getApplicationType() === 'FE') {
            return;
        }
        // Add JavaScript
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/ThemesBackendTca');
        // Add CSS
        $extensionFile = 'Resources/Public/Stylesheet/ThemesBackendTca.css';
        $absolutePath = ExtensionManagementUtility::extPath('themes', $extensionFile);
        $filename = PathUtility::getAbsoluteWebPath($absolutePath);
        $pageRenderer->addCssFile($filename, 'stylesheet', 'screen');
    }
}
