<?php

namespace KayStrobach\Themes\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Access constants
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 *
 * @deprecated readd constantsviewhelper to ensure compatibility to old themes
 *
 */
class ConstantViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('constant', 'string', 'the constant path', false, '');
    }

    /**
     * Gets a constant
     *
     * @param string $constant The name of the constant
     * @return string Constant-Value
     *
     * = Examples =
     *
     * <code title="Example">
     * <theme:constant constant="themes.configuration.baseurl" />
     * </code>
     * <output>
     * http://yourdomain.tld/
     * (depending on your domain)
     * </output>
     */
    public function render()
    {
        $constant = $this->arguments['constant'];

        $pageWithTheme   = \KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility::find($this->getFrontendController()->id);
        $pageLanguage    = (int)GeneralUtility::_GP('L');
        $flatSetup = $this->getFrontendController()->tmpl->flatSetup;

        // If flatSetup not available and not cached, generate it!
        if (!isset($flatSetup) || !is_array($flatSetup)) {
            $this->getFrontendController()->tmpl->generateConfig();
            $flatSetup = $this->getFrontendController()->tmpl->flatSetup;
        }

        // check if there is a value and return it
        if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
            return $this->getFrontendController()->tmpl->substituteConstants($flatSetup[$constant]);
        }
        return null;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
