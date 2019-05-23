<?php

namespace KayStrobach\Themes\ViewHelpers\Variable;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @author Thomas Deuling <typo3@coding.ms>, coding.ms
 */
class PopBehaviourViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'Name', true);
    }

    /**
     * Pop the behaviour with the variable in $name.
     *
     * @return void
     */
    public function render()
    {
        $name = $this->arguments['name'];
        if (false === $this->templateVariableContainer->exists('themes')) {
            return;
        } else {
            $themes = $this->templateVariableContainer->get('themes');
            if (isset($themes['behaviour'])) {
                $value = '';
                if (isset($themes['behaviour']['css'])) {
                    if (isset($themes['behaviour']['css'][$name])) {
                        $value = $themes['behaviour']['css'][$name];
                        unset($themes['behaviour']['css'][$name]);
                    }
                }
                if (isset($themes['behaviour']['css2key'])) {
                    if (isset($themes['behaviour']['css2key'][$value])) {
                        unset($themes['behaviour']['css2key'][$value]);
                    }
                }
                $themes['behaviour']['key2css'] = $themes['behaviour']['css'];
                $themes['behaviour']['cssClasses'] = implode(' ', $themes['behaviour']['css']);
                // Write back
                $this->templateVariableContainer->remove('themes');
                $this->templateVariableContainer->add('themes', $themes);
            }
        }
    }

}
