<?php

namespace KayStrobach\Themes\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Access constants
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
class ConstantViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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
	public function render($constant = '') {

		$pageWithTheme   = \KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility::find($GLOBALS['TSFE']->id);
		$pageLanguage    = (int)GeneralUtility::_GP('L');
		// instantiate the cache
		$cache           = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('themes_cache');
		$cacheLifeTime = 60 * 60 * 24 * 7 * 365 * 20;
		$cacheIdentifierString = 'theme-of-page-' . $pageWithTheme . '-of-language-' . $pageLanguage;
		$cacheIdentifier = sha1($cacheIdentifierString);

		// If flatSetup is available, cache it
		$flatSetup = $GLOBALS['TSFE']->tmpl->flatSetup;
		if ((isset($flatSetup) && (is_array($flatSetup)) && (count($flatSetup) > 0))) {
			$cache->set(
				$cacheIdentifier,
				$flatSetup,
				array(),
				$cacheLifeTime
			);
		} else {
			$flatSetup = $cache->get($cacheIdentifier);
		}

		// If flatSetup not available and not cached, generate it!
		if (!isset($flatSetup) || !is_array($flatSetup)) {
			$GLOBALS['TSFE']->tmpl->generateConfig();
			$flatSetup = $GLOBALS['TSFE']->tmpl->flatSetup;
			$cache->set(
				$cacheIdentifier,
				$flatSetup,
				array(),
				$cacheLifeTime
			);
		}

		// check if there is a value and return it
		if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
			return $flatSetup[$constant];
		}
		return NULL;
	}
}
