<?php

namespace KayStrobach\Themes\ViewHelpers;

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
 * Access constants.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ConstantViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('constant', 'string', 'The constant to process');
    }

    /**
     * Gets a constant.
     *
     * @param string $constant The name of the constant
     *
     * @return string Constant-Value
     *
     * = Examples =
     *
     * <code title="Example">
     * <theme:constant constant="themes.configuration.baseurl" />
     * </code>
     * <output>
     * http://yourdomain.tld/
     * (depending on your domain)
     * </output>
     */
    public function render(): string
    {
        $constant = $this->arguments['constant'];

        $pageWithTheme = \KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility::find($this->getFrontendController()->id);
        $pageLanguage = (int)GeneralUtility::_GP('L');
        // instantiate the cache
        /** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache */
        $cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('themes_cache');
        $cacheLifeTime = 60 * 60 * 24 * 7 * 365 * 20;
        $cacheIdentifierString = 'theme-of-page-' . $pageWithTheme . '-of-language-' . $pageLanguage;
        $cacheIdentifier = sha1($cacheIdentifierString);

        // If flatSetup is available, cache it
        $flatSetup = $this->getFrontendController()->tmpl->flatSetup;
        if ((isset($flatSetup) && (is_array($flatSetup)) && (count($flatSetup) > 0))) {
            $cache->set(
                $cacheIdentifier, $flatSetup, [
                'page-' . $this->getFrontendController()->id,
            ], $cacheLifeTime
            );
        } else {
            $flatSetup = $cache->get($cacheIdentifier);
        }

        // If flatSetup not available and not cached, generate it!
        if (!isset($flatSetup) || !is_array($flatSetup)) {
            $this->getFrontendController()->tmpl->generateConfig();
            $flatSetup = $this->getFrontendController()->tmpl->flatSetup;
            $cache->set(
                $cacheIdentifier, $flatSetup, [
                'page-' . $this->getFrontendController()->id,
            ], $cacheLifeTime
            );
        }

        // check if there is a value and return it
        if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
            return $this->getFrontendController()->tmpl->substituteConstants($flatSetup[$constant]);
        }
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

}
