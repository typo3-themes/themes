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
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'array', 'Subject');
        $this->registerArgument('key', 'string', 'Key');
    }

    /**
     *
     * @return array|null
     */
    public function render()
    {
        $subject = $this->arguments['subject'];
        $key = $this->arguments['key'];

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
