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
		// Check the own page first
		if (CheckPageUtility::hasThemeableSysTemplateRecord($pid)) {
			return $pid;
		}

		// ..then the rootline pages
		$rootLineUtility = new RootlineUtility($pid);
		$pages = $rootLineUtility->get();
		foreach ($pages as $page) {
			if (CheckPageUtility::hasThemeableSysTemplateRecord($page['pid'])) {
				return $page['pid'];
			}
		}
		return NULL;
	}

}
