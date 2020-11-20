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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Hooks into the theme repo to load the list of themes.
 */
class ThemesDomainRepositoryThemeRepositoryInitHook
{

    /**
     * Add all available themes to the Themes repository
     *
     * @param array $params
     * @param $pObj \KayStrobach\Themes\Domain\Repository\ThemeRepository
     * @throws \Exception
     */
    public function init(&$params, $pObj)
    {
        //
        // Themes configuration
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $themesConfiguration = $extensionConfiguration->get('themes');
        $startWith = $themesConfiguration['themeExtensionsStartWith'];
        //
        // Get all available extensions, excluding system extensions
        $extensionsToCheck = array_diff(
            ExtensionManagementUtility::getLoadedExtensionListArray(),
            scandir(Environment::getPublicPath() . '/typo3/sysext')
        );
        //
        // Check extensions, which are worth to check
        foreach ($extensionsToCheck as $extensionName) {
            if (trim($startWith) === '' || substr($extensionName, 0, strlen($startWith)) === $startWith) {
                $extPath = ExtensionManagementUtility::extPath($extensionName);
                if (file_exists($extPath . 'Meta/theme.yaml')) {
                    if (file_exists($extPath . 'Configuration/TypoScript/setup.typoscript')) {
                        $pObj->add(new Theme($extensionName));
                    }
                }
            }
        }
    }
}
