<?php

declare(strict_types=1);

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

use Doctrine\DBAL\DBALException;
use KayStrobach\Themes\Utilities\ThemeEnabledCondition;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Is a theme enabled?
 *
 * @author Thomas Deuling <typo3@coding.ms>, Kay Strobach
 */
class ThemeEnabledConditionViewHelper extends AbstractConditionViewHelper
{
    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     *
     * @return bool
     * @throws DBALException
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ThemeEnabledCondition::isThemeEnabled($arguments['theme']);
    }

    /**
     * Initializes the "theme" argument.
     */
    public function initializeArguments()
    {
        $this->registerArgument('theme', 'string', 'The theme');
    }
}
