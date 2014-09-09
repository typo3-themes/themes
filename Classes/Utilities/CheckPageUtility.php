<?php

namespace KayStrobach\Themes\Utilities;

/**
 * Class CheckPageUtility
 *
 * @package KayStrobach\Themes\Utilities
 */
class CheckPageUtility {

	/**
	 * @param $pid
	 * @return bool
	 */
	public static function hasTheme($pid) {
		$templateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'sys_template',
			'pid = ' . (integer) $pid . ' AND deleted=0 AND hidden=0 AND root=1 AND tx_themes_skin <> ""'
		);
		if ($templateCount > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $pid
	 * @return bool
	 */
	public static function hasThemeableSysTemplateRecord($pid) {
		$templateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'sys_template',
			'pid = ' . (integer) $pid . ' AND deleted=0 AND hidden=0 AND root=1'
		);
		if ($templateCount > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $pid
	 * @return bool|integer
	 */
	public static function getThemeableSysTemplateRecord($pid) {
		$templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'sys_template',
			'pid = ' . (integer) $pid . ' AND deleted=0 AND hidden=0 AND root=1',
			'',
			'sorting ASC'
		);
		if (is_array($templates)) {
			return $templates['uid'];
		}
		return FALSE;
	}
}
