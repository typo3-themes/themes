<?php

namespace KayStrobach\Themes\ViewHelpers;

use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Access constants.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ConstantViewHelper extends AbstractViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('constant', 'string', 'Constant name', true);
    }

    /**
     * Gets a constant.
     *
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
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string Constant-Value
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $constant = $arguments['constant'];

        $pageWithTheme = FindParentPageWithThemeUtility::find(self::getFrontendController()->id);
        $pageLanguage = (int)GeneralUtility::_GP('L');
        // instantiate the cache
        /** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache */
        $cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('themes_cache');
        $cacheLifeTime = 60 * 60 * 24 * 7 * 365 * 20;
        $cacheIdentifierString = 'theme-of-page-' . $pageWithTheme . '-of-language-' . $pageLanguage;
        $cacheIdentifier = sha1($cacheIdentifierString);

        // If flatSetup is available, cache it
        $flatSetup = self::getFrontendController()->tmpl->flatSetup;
        if ((isset($flatSetup) && (is_array($flatSetup)) && (count($flatSetup) > 0))) {
            $cache->set(
                $cacheIdentifier,
                $flatSetup,
                [
                    'page-' . self::getFrontendController()->id,
                ],
                $cacheLifeTime
            );
        } else {
            $flatSetup = $cache->get($cacheIdentifier);
        }

        // If flatSetup not available and not cached, generate it!
        if (!isset($flatSetup) || !is_array($flatSetup)) {
            self::getFrontendController()->tmpl->generateConfig();
            $flatSetup = self::getFrontendController()->tmpl->flatSetup;
            $cache->set(
                $cacheIdentifier,
                $flatSetup,
                [
                    'page-' . self::getFrontendController()->id,
                ],
                $cacheLifeTime
            );
        }

        // check if there is a value and return it
        if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
            return self::getFrontendController()->tmpl->substituteConstants($flatSetup[$constant]);
        }
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public static function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
