<?php
namespace KayStrobach\Themes\XClass;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class automatically adds the theme TSConfig for the current page
 * to the Page TSConfig either by using the XCLASS mechanism for older
 * TYPO3 versions or a signal slot.
 */
class TsConfigParser extends \TYPO3\CMS\Backend\Configuration\TsConfigParser {

	/**
	 * Adds the theme Page TSConfig to the TSConfig array.
	 *
	 * Will be called when the signal slog is used (since 6.2.4).
	 *
	 * @param array $TSdataArray
	 * @param int $id
	 * @param array $rootLine
	 * @param bool $returnPartArray
	 * @return void
	 */
	public function modifyTsDataArray(
		&$TSdataArray,
		&$id,
		/** @noinspection PhpUnusedParameterInspection */
		&$rootLine,
		/** @noinspection PhpUnusedParameterInspection */
		&$returnPartArray
	) {
		$themesTsConfig = $this->getTsConfigForPage($id);
		if ($themesTsConfig !== '') {
			$TSdataArray['themesTSConfig'] = $themesTsConfig;
		}
	}

	/**
	 * Parses the passed TS-Config using conditions and caching.
	 *
	 * Will be called when the XCLASS mechanism is used (pre 6.2.4).
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
		$tsconfig = $this->getTsConfigForPage($id);
		if($tsconfig !== '') {

            // New
            // 2014-06-06 tdeuling@coding.ms
            // Parse TSConfig includes
            $TSdataArray = array();
            $TSdataArray['themesTSConfig'] = $tsconfig;
            $TSdataArray = \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::checkIncludeLines_array($TSdataArray);
            $buffer = implode(LF . LF . '[GLOBAL]' . LF . LF, $TSdataArray). LF . LF . '[GLOBAL]'. LF . LF . $TStext;

            // Old
            //$buffer = $theme->getTSConfig() . "\n\n[GLOBAL]\n\n" . $TStext;
			return parent::parseTSconfig($buffer, $type, $id, $rootLine);
		} else {
			return parent::parseTSconfig($TStext, $type, $id, $rootLine);
		}
	}

	/**
	 * Retrieves the theme TSConfig for the given page.
	 *
	 * @param int $pageUid
	 * @return string The found TSConfig or an empty string.
	 */
	protected function getTsConfigForPage($pageUid) {

		$pageUid = (int)$pageUid;
		if ($pageUid === 0) {
			return '';
		}

		/** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
		$themeRepository = GeneralUtility::makeInstance('KayStrobach\Themes\\Domain\\Repository\\ThemeRepository');
		$theme = $themeRepository->findByPageOrRootline($pageUid);

		if (!isset($theme)) {
			return '';
		}

		return $theme->getTSConfig();
	}
}