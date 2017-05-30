<?php


namespace KayStrobach\Themes\Utilities;

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
        $pageId = intval(GeneralUtility::_GET('id'));
        /** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
        $themeRepository = GeneralUtility::makeInstance('KayStrobach\Themes\Domain\Repository\ThemeRepository');
        /** @var \KayStrobach\Themes\Domain\Model\Theme $themeOfPage */
        $themeOfPage = $themeRepository->findByPageOrRootline($pageId);

        return ($themeOfPage !== null) && ($themeOfPage->getExtensionName() === $theme);
    }
}
