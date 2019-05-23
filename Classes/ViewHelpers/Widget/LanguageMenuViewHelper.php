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

use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;
use KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController;

/**
 * Provides a Language Menu.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class LanguageMenuViewHelper extends AbstractWidgetViewHelper
{
    /**
     * @var \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController
     */
    protected $controller;

    /**
     * @param \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController $controller
     *
     * @return void
     */
    public function injectController(LanguageMenuController $controller)
    {
        $this->controller = $controller;
    }

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
        return $this->initiateSubRequest();
    }
}
