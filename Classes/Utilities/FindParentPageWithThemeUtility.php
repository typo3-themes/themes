<?php

namespace KayStrobach\Themes\Utilities;

use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * @todo missing docblock
 */
class FindParentPageWithThemeUtility {

	/**
	 * @todo missing docblock
	 */
	public static function find($pid) {
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
