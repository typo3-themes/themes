<?php

namespace KayStrobach\Themes\Hook;

/**
 * Class PageNotFoundHandlingHook
 * @package KayStrobach\Themes\Hook
 */

class IconUtilityHook {
	public function overrideIconOverlay($table, &$row, &$status) {
		$templateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'sys_template',
			'pid = ' . (integer) $row['uid'] . ' AND deleted=0 AND hidden=0 AND tx_themes_skin <> ""'
		);
		if($templateCount > 0) {
			$status['themefound'] = TRUE;
		}

	}
}