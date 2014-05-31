<?php

namespace KayStrobach\Themes\Hook;
use KayStrobach\Themes\Utilities\CheckPageUtility;

/**
 * Class PageNotFoundHandlingHook
 * @package KayStrobach\Themes\Hook
 */

class IconUtilityHook {
	public function overrideIconOverlay($table, &$row, &$status) {
		if(CheckPageUtility::hasTheme($row['uid']) > 0) {
			$status['themefound'] = TRUE;
		}
	}
}