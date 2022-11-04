<?php

namespace KayStrobach\Themes\ViewHelpers\Widget;

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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Provides a Language Menu.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class LanguageMenuViewHelper extends AbstractViewHelper
{
    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     * @see isChildrenEscapingEnabled()
     *
     * Note: If this is NULL the value of $this->escapingInterceptorEnabled is considered for backwards compatibility
     *
     * @var bool
     * @api
     */
    protected $escapeChildren = false;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     * @see isOutputEscapingEnabled()
     *
     * @var bool
     * @api
     */
    protected $escapeOutput = false;

    /**
     * initialize the arguments of the viewHelper.
     *
     * @return void
     */
    public function initializeArguments()
    {
        /**
         * @todo Remove deprecated arguments
         */
        $this->registerArgument('availableLanguages', 'string', 'Comma separated list of integers of the languages', false, '');
        $this->registerArgument('currentLanguageUid', 'int', 'Id of the current language', false, 0);
        $this->registerArgument('defaultLanguageIsoCodeShort', 'string', 'IsoCode of the default language', false, 'en');
        $this->registerArgument('defaultLanguageLabel', 'string', 'Label of the default language', false, 'English');
        $this->registerArgument('defaultLanguageFlag', 'string', 'Flag of the default language', false, 'gb');
        //
        $this->registerArgument('flagIconPath', 'string', 'directory containing the flags', false, '/typo3/sysext/core/Resources/Public/Icons/Flags/SVG/');
        $this->registerArgument('flagIconFileExtension', 'string', 'file extension of the flag files', false, 'svg');
    }

    /**
     * @return string
     */
    public function render()
    {
        return '<!-- this widget is currently not available -->';
    }
}
