<?php

namespace KayStrobach\Themes\ViewHelpers\Format;

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
 * Replaces $substring in $content with $replacement.
 */
class ReplaceViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('substring', 'string', 'Substring', true);
        $this->registerArgument('content', 'string', 'Content');
        $this->registerArgument('replacement', 'string', 'Replacement');
        $this->registerArgument('count', 'integer', 'Count');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $substring = $this->arguments['substring'];
        $content = $this->arguments['content'];
        $replacement = $this->arguments['replacement'];
        $count = $this->arguments['count'];
        if (null === $content) {
            $content = $this->renderChildren();
        }
        return str_replace($substring, $replacement, $content, $count);
    }

}
