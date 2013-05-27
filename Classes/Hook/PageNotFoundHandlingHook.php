<?php

/**
 * @Todo implement a default page not found handler, which allows the user to init the page easily !
 *
 */

class Tx_Themes_Hook_PageNotFoundHandlingHook {
	/**
	 * params consists of the following keys!
	 *
	 * 'currentUrl' => \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'),
	 * 'reasonText' => $reason,
	 * 'pageAccessFailureReasons' => $this->getPageAccessFailureReasons()
	 *
	 * @param $params
	 * @param $pObj
	 */
	function main(&$params, &$pObj) {
		print_r($params);
		die('dada');
	}
}