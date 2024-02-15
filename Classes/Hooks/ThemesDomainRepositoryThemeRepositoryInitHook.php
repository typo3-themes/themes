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

use Exception;
use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks into the theme repo to load the list of themes.
 */
class ThemesDomainRepositoryThemeRepositoryInitHook
{
    /**
     * Add all available themes to the Themes repository
     *
     * @throws Exception
     */
    public function init(array &$params, ThemeRepository $pObj): void
    {
        //
        // Themes configuration
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $themesConfiguration = $extensionConfiguration->get('themes');
        $startWith = $themesConfiguration['themeExtensionsStartWith'];
        //
        // Check extensions, which are worth to check
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extensionName) {
            if (trim((string)$startWith) === '' || str_starts_with((string)$extensionName, (string)$startWith)) {
                $extPath = ExtensionManagementUtility::extPath($extensionName);
                if (file_exists($extPath . 'Meta/theme.yaml')) {
                    if (file_exists($extPath . 'Configuration/TypoScript/setup.typoscript') || file_exists($extPath . 'Configuration/TypoScript/setup.txt')) {
                        $pObj->add(new Theme($extensionName));
                    }
                }
            }
        }
    }
}
