<?php

namespace KayStrobach\Themes\Utilities;

use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Class FindParentPageWithThemeUtility
 *
 * @package KayStrobach\Themes\Utilities
 */
class FindParentPageWithThemeUtility {

	/**
	 * @todo missing docblock
	 */
	public static function find($pid) {
		$rootLineUtility = new RootlineUtility($pid);
		$pages = $rootLineUtility->get();
		// Check the own page first
		if (CheckPageUtility::hasThemeableSysTemplateRecord($GLOBALS['TSFE']->id)) {
			return $GLOBALS['TSFE']->id;
		}
		// ..then the rootline pages
		foreach ($pages as $page) {
			if (CheckPageUtility::hasThemeableSysTemplateRecord($page['pid'])) {
				return $page['pid'];
			}
		}
		return NULL;
	}

}
