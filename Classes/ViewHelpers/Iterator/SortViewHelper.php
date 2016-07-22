<?php

namespace KayStrobach\Themes\ViewHelpers\Iterator;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class SortViewHelper.
 *
 * Sorts a given array by a given key
 */
class SortViewHelper extends AbstractViewHelper
{
    /**
     * @param array  $subject
     * @param string $key
     *
     * @return array|null
     */
    public function render($subject = null, $key = 'label')
    {
        $this->arguments['key'] = $key;
        if (null === $subject) {
            $subject = $this->renderChildren();
        }
        $sorted = null;
        if (true === is_array($subject)) {
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
    protected function sortArray($array)
    {
        usort($array, [$this, 'compare']);

        return $array;
    }

    public function compare($a, $b)
    {
        return strcasecmp($a[$this->arguments['key']], $b[$this->arguments['key']]);
    }
}
