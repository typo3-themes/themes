<?php

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

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ThemeEnabledCondition
 * @package KayStrobach\Themes\Utilities
 */
class ThemeEnabledCondition
{
    /**
     * Check if theme is enabled
     *
     * @param string $theme
     * @return boolean
     */
    public static function isThemeEnabled($theme = '')
    {
        $pageId = (int)GeneralUtility::_GET('id');
        /** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
        $themeRepository = GeneralUtility::makeInstance(ThemeRepository::class);
        /** @var \KayStrobach\Themes\Domain\Model\Theme $themeOfPage */
        $themeOfPage = $themeRepository->findByPageOrRootline($pageId);
        return ($themeOfPage !== null) && ($themeOfPage->getExtensionName() === $theme);
    }
}
