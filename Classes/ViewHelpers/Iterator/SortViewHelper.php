<?php

namespace KayStrobach\Themes\ViewHelpers\Iterator;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class SortViewHelper.
 *
 * Sorts a given array by a given key
 */
class SortViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize Arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('subject', 'mixed', 'Subject', false, null);
        $this->registerArgument('key', 'string', 'Key', false, null);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array|null
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $subject = $arguments['subject'];
        $key = $arguments['key'];

        if (null === $subject) {
            $subject = $renderChildrenClosure();
        }
        $sorted = null;
        if (true === is_array($subject)) {
            $sorted = self::sortArray($subject, $key);
        }

        return $sorted;
    }

    /**
     * Sort an array.
     *
     * @param array $array
     * @param string $key
     *
     * @return array
     */
    protected static function sortArray($array, $key)
    {
        usort($array, function ($a, $b) use ($key) {
            strcasecmp($a[$key], $b[$key]);
        });

        return $array;
    }
}
