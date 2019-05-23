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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hooks into the theme repo to load the list of themes.
 */
class ThemesDomainRepositoryThemeRepositoryInitHook
{
    /**
     * @var array
     *
     * @todo find a more flexible solution
     */
    protected $ignoredExtensions = [
        'themes',
        'skinselector_content',
        'skinpreview',
        'templavoila',
        'piwik',
        'piwikintegration',
        'templavoila_framework',
        'be_acl',
        'sitemgr',
        'sitemgr_template',
        'sitemgr_fesettings',
        'sitemgr_fe_notfound',
        'cal',
        'extension_builder',
        'coreupdate',
        'contextswitcher',
        'extdeveval',
        'powermail',
        'kickstarter',
        'tt_news',
        'dyncss',
        'dyncss_less',
        'dyncss_scss',
        'dyncss_turbine',
        'static_info_tables',
        'realurl',
    ];

    /**
     * hook function.
     *
     * @param $params
     * @param $pObj
     *
     * @return void
     *
     * @todo add a more explaining description why this hook is required
     */
    public function init(&$params, $pObj)
    {
        // exclude extensions, which are not worth to check them
        $extensionsToCheck = array_diff(
            ExtensionManagementUtility::getLoadedExtensionListArray(),
            $this->ignoredExtensions,
            scandir(PATH_typo3.'sysext')
        );

        // check extensions, which are worth to check
        foreach ($extensionsToCheck as $extensionName) {
            $extPath = ExtensionManagementUtility::extPath($extensionName);
            if (file_exists($extPath . 'Meta/theme.yaml') && (file_exists($extPath . 'Configuration/TypoScript/setup.txt') || file_exists($extPath . 'Configuration/TypoScript/setup.typoscript'))) {
                $pObj->add(new Theme($extensionName));
            }
        }
    }
}
