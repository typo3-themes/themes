<?php
namespace KayStrobach\Themes\ViewHelpers\Format;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Replaces $substring in $content with $replacement.
 */
class ReplaceViewHelper extends AbstractViewHelper
{

    /**
     * @param string $substring
     * @param string $content
     * @param string $replacement
     * @param integer $count
     * @return string
     */
    public function render($substring, $content = NULL, $replacement = '', $count = NULL)
    {
        if (NULL === $content) {
            $content = $this->renderChildren();
        }
        return str_replace($substring, $replacement, $content, $count);
    }

}
