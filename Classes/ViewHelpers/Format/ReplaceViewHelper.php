<?php

namespace KayStrobach\Themes\ViewHelpers\Format;

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
