<?php

namespace KayStrobach\Themes\Utilities;

/**
 * @todo missing docblock
 */
class CheckPageUtility {

	/**
	 * @todo missing docblock
	 */
	public static function hasTheme($pid) {
		$templateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'sys_template',
			'pid = ' . (integer) $pid . ' AND deleted=0 AND hidden=0 AND root=1 AND tx_themes_skin <> ""'
		);
		if ($templateCount > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @todo missing docblock
	 */
	public static function hasThemeableSysTemplateRecord($pid) {
		$templateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'sys_template',
			'pid = ' . (integer) $pid . ' AND deleted=0 AND hidden=0 AND root=1'
		);
		if ($templateCount > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
