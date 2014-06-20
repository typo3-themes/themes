<?php

namespace KayStrobach\Themes\Hook;

use KayStrobach\Themes\Utilities\CheckPageUtility;

/**
 * Class PageNotFoundHandlingHook
 * @package KayStrobach\Themes\Hook
 */
class IconUtilityHook {

	/**
	 * @todo missing docblock
	 */
	public function overrideIconOverlay($table, &$row, &$status) {
		if ($table === 'pages') {
			if (CheckPageUtility::hasTheme($row['uid']) > 0) {
				$status['themefound'] = TRUE;
			}
		}
	}

}
