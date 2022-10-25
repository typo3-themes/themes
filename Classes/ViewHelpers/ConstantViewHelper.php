<?php

declare(strict_types=1);

namespace KayStrobach\Themes\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * ConstantViewHelper
 */
class ConstantViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('constant', 'string', 'TypoScript constant');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $constant = trim($arguments['constant']);
        if (($GLOBALS['TSFE']->tmpl->flatSetup === null) || (!is_array($GLOBALS['TSFE']->tmpl->flatSetup)) || (count($GLOBALS['TSFE']->tmpl->flatSetup) === 0)) {
            $GLOBALS['TSFE']->tmpl->generateConfig();
        }

        return $GLOBALS['TSFE']->tmpl->flatSetup[$constant] ?? '';
    }
}
