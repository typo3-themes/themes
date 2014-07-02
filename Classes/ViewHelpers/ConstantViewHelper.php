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
		$constantValue = NULL;
		// instantiate the cache
		$cache = $GLOBALS['typo3CacheManager']->getCache('themes_cache');
		// If flatSetup is available, cache it
		if (isset($GLOBALS['TSFE']->tmpl->flatSetup[$constant])) {
			foreach($GLOBALS['TSFE']->tmpl->flatSetup as $constantName=>$value) {
				// Only cache themes-constants
				if(substr($constantName, 0, 7)=='themes.') {
					// Cache-Identifier can be the same as the constant-name
					// because it's already unique
					// But we use also a sha1, because the constant-name could be longer than varchar(250)
					$cacheIdentifier = sha1($constantName);
					// save constant in cache
					// lifetime= 0 doesnt work
					// $lifetime = 0;
					$lifetime = 60*60*24*7*365*20;
					$cache->set($cacheIdentifier, $value, array(), $lifetime);
				}
			}
			// Finally return the constant value
			$constantValue = $GLOBALS['TSFE']->tmpl->flatSetup[$constant];
		} else {
			// otherwise, get constant value from cache
			// Cache-Identifier can be the same as the constant-name
			// because it's already unique
			// But we use also a sha1, because the constant-name could be longer than varchar(250)
			$cacheIdentifier = sha1($constant);

			// try to find the cached content
			if (($value = $cache->get($cacheIdentifier)) !== FALSE) {
				$constantValue = $value;
			}
			// Debugging
			else {
				$constantValue = $constant.'('.$cacheIdentifier.'|'.serialize($value).')';
			}
		}
		return $constantValue;
	}
}
