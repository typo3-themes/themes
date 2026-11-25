<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Themes\ViewHelpers\Widget;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LanguageMenuViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    protected $escapeChildren = false;

    // the old widget had arguments, the stub accepts and ignores all of them
    public function validateAdditionalArguments(array $arguments): void
    {
    }

    public function render(): string
    {
        return '<!-- Language Menu Widget is deprecated and will be removed later, use the info from the site config now! -->';
    }
}
