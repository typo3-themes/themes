<?php

namespace KayStrobach\Themes\ViewHelpers;

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
		// instantiate the cache
		$cache           = $GLOBALS['typo3CacheManager']->getCache('themes_cache');
		$cacheIdentifier = sha1('theme-of-page-' . $pageWithTheme);

		$flatSetup = $GLOBALS['TSFE']->tmpl->flatSetup;

		// If flatSetup is available, cache it
		if ((isset($flatSetup) && (is_array($flatSetup)) && (count($flatSetup) > 0))) {
			$cache->set(
				$cacheIdentifier,
				$flatSetup,
				array(),
				60 * 60 * 24 * 7 * 365 * 20
			);
		} else {
			$flatSetup = $cache->get($cacheIdentifier);
		}

		// check if there is a value and return it
		if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
			return $flatSetup[$constant];
		} else {
			return NULL;
		}
	}
}
