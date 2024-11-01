<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Themes\ViewHelpers\Widget;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class LanguageMenuViewHelper extends AbstractViewHelper
{

    protected $escapeOutput = false;
    protected $escapeChildren = false;

    public function validateAdditionalArguments(array $arguments) {}
    public function render()
    {
        return '<!-- Language Menu Widget is deprecated and will be removed later, use the info from the site config now! -->';
    }
}
