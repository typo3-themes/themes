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

		$pageWithTheme   = \KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility::find($this->getFrontendController()->id);
		$pageLanguage    = (int)GeneralUtility::_GP('L');
		// instantiate the cache
		/** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache */
		$cache           = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('themes_cache');
		$cacheLifeTime = 60 * 60 * 24 * 7 * 365 * 20;
		$cacheIdentifierString = 'theme-of-page-' . $pageWithTheme . '-of-language-' . $pageLanguage;
		$cacheIdentifier = sha1($cacheIdentifierString);

		// If flatSetup is available, cache it
		$flatSetup = $this->getFrontendController()->tmpl->flatSetup;
		if ((isset($flatSetup) && (is_array($flatSetup)) && (count($flatSetup) > 0))) {
			$cache->set(
				$cacheIdentifier,
				$flatSetup,
				array(
						'page-' . $this->getFrontendController()->id
				),
				$cacheLifeTime
			);
		} else {
			$flatSetup = $cache->get($cacheIdentifier);
		}

		// If flatSetup not available and not cached, generate it!
		if (!isset($flatSetup) || !is_array($flatSetup)) {
			$this->getFrontendController()->tmpl->generateConfig();
			$flatSetup = $this->getFrontendController()->tmpl->flatSetup;
			$cache->set(
				$cacheIdentifier,
				$flatSetup,
				array(
					'page-' . $this->getFrontendController()->id
				),
				$cacheLifeTime
			);
		}

		// check if there is a value and return it
		if ((is_array($flatSetup)) && (array_key_exists($constant, $flatSetup))) {
			return $this->getFrontendController()->tmpl->substituteConstants($flatSetup[$constant]);
		}
		return NULL;
	}

	/**
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	public function getFrontendController() {
		return $GLOBALS['TSFE'];
	}
}
