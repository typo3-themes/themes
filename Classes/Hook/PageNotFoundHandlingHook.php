<?php

namespace KayStrobach\Themes\Hook;

/**
 * @Todo implement a default page not found handler, which allows the user to init the page easily !
 *
 * check wether the page is initialized, of not allow login and get started up easily,
 * if configured show the theme error page.
 *
 */
class PageNotFoundHandlingHook {

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
		die();
	}

}
