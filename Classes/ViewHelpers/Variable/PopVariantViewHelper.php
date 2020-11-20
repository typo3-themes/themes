<?php

namespace KayStrobach\Themes\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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


/**
 * @author Thomas Deuling <typo3@coding.ms>, coding.ms
 */
class PopVariantViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'Name', true);
    }

    /**
     * Pop the variant with the variable in $name.
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
            if (isset($themes['variants'])) {
                $value = '';
                if (isset($themes['variants']['css'])) {
                    if (isset($themes['variants']['css'][$name])) {
                        $value = $themes['variants']['css'][$name];
                        unset($themes['variants']['css'][$name]);
                    }
                }
                if (isset($themes['variants']['css2key'])) {
                    if (isset($themes['variants']['css2key'][$value])) {
                        unset($themes['variants']['css2key'][$value]);
                    }
                }
                $themes['variants']['key2css'] = $themes['variants']['css'];
                $themes['variants']['cssClasses'] = implode(' ', $themes['variants']['css']);
                // Write back
                $this->templateVariableContainer->remove('themes');
                $this->templateVariableContainer->add('themes', $themes);
            }
        }
    }

}
