<?php

class ux_t3lib_TSparser_TSconfig extends t3lib_TSparser_TSconfig{

	/**
	 * Parses the passed TS-Config using conditions and caching
	 *
	 * @param	string		$TStext: The TSConfig being parsed
	 * @param	string		$type: The type of TSConfig (either "userTS" or "PAGES")
	 * @param	integer		$id: The uid of the page being handled
	 * @param	array		$rootLine: The rootline of the page being handled
	 * @return	array		Array containing the parsed TSConfig and a flag wheter the content was retrieved from cache
	 * @see t3lib_TSparser
	 */
	public function parseTSconfig($TStext, $type, $id = 0, array $rootLine = array()) {
		// @todo add caching here!
		/**
		 * @var Tx_Themes_Domain_Repository_ThemeRepository
		 */
		$themeRepository = t3lib_div::makeInstance('Tx_Themes_Domain_Repository_ThemeRepository');
		$theme = $themeRepository->findByPageOrRootline($id);
		if($theme !== NULL) {
			$buffer = $theme->getTSConfig() . "\n\n[GLOBAL]\n\n" . $TStext;
			return parent::parseTSconfig($buffer, $type, $id, $rootLine);
		} else {
			return parent::parseTSconfig($TStext, $type, $id, $rootLine);
		}
	}
}