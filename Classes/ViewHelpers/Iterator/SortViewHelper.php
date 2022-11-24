<?php

declare(strict_types=1);

namespace KayStrobach\Themes\ViewHelpers\Iterator;

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
 * Class SortViewHelper.
 *
 * Sorts a given array by a given key
 */
class SortViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'array', 'Subject');
        $this->registerArgument('key', 'string', 'Key');
    }

    /**
     * @return array|null
     */
    public function render(): ?array
    {
        $subject = $this->arguments['subject'];
        $key = $this->arguments['key'];

        $this->arguments['key'] = $key;
        if (null === $subject) {
            $subject = $this->renderChildren();
        }
        $sorted = null;
        if (is_array($subject)) {
            $sorted = $this->sortArray($subject);
        }
        return $sorted;
    }

    /**
     * Sort an array.
     *
     * @param array $array
     *
     * @return array
     */
    protected function sortArray(array $array): array
    {
        usort($array, [$this, 'compare']);
        return $array;
    }

    public function compare($a, $b): int
    {
        return strcasecmp($a[$this->arguments['key']], $b[$this->arguments['key']]);
    }
}
