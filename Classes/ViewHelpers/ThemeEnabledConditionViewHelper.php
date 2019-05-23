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

use KayStrobach\Themes\Utilities\ThemeEnabledCondition;

/**
 * Is a theme enabled?
 *
 * @author Thomas Deuling <typo3@coding.ms>, Kay Strobach
 */
class ThemeEnabledConditionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{
    /**
     * Initializes the "theme" argument.
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('theme', 'string', 'The theme');
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     *
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        return ThemeEnabledCondition::isThemeEnabled($arguments['theme']);
    }
}
