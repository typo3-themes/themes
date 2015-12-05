<?php

namespace KayStrobach\Themes\Hooks;

use KayStrobach\Themes\Utilities\CheckPageUtility;

/**
 * Class PageNotFoundHandlingHook
 *
 * @package KayStrobach\Themes\Hooks
 */
class IconUtilityHook {

	/**
	 * adds the overlay icon to a page with a theme set
	 *
	 * @param $table
	 * @param $row
	 * @param $status
	 * @return void
	 */
	public function overrideIconOverlay($table, &$row, &$status) {
		if ($table === 'pages') {
			if (CheckPageUtility::hasTheme($row['uid']) > 0) {
				$status['themefound'] = TRUE;
			}
		}
	}

}
