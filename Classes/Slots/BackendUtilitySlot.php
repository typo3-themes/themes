<?php
namespace KayStrobach\Themes\Slots;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class automatically adds the theme TSConfig for the current page
 * to the Page TSConfig either by using a signal slot.
 */
class BackendUtilitySlot extends \TYPO3\CMS\Backend\Configuration\TsConfigParser {

	/**
	 * Retrieves the theme TSConfig for the given page.
	 *
	 * @param $typoscriptDataArray
	 * @param int $pageUid
	 * @param $rootLine
	 * @param $returnPartArray
	 * @return string The found TSConfig or an empty string.
	 */
	public function getPagesTsConfigPreInclude($typoscriptDataArray, $pageUid, $rootLine, $returnPartArray) {

		$pageUid = (int)$pageUid;

		if ($pageUid === 0) {
			return NULL;
		}

		/** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
		$themeRepository = GeneralUtility::makeInstance('KayStrobach\Themes\\Domain\\Repository\\ThemeRepository');
		$theme = $themeRepository->findByPageOrRootline($pageUid);

		if (!isset($theme)) {
			return '';
		}

		$defaultDataArray['defaultPageTSconfig'] = array_shift($typoscriptDataArray);
		array_unshift($typoscriptDataArray, $theme->getTypoScriptConfig());
		$typoscriptDataArray = $defaultDataArray + $typoscriptDataArray;

		return array(
			$typoscriptDataArray,
			$pageUid,
			$rootLine,
			$returnPartArray
		);
	}
}
