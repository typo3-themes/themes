<?php

namespace KayStrobach\Themes\Utilities;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TsParserUtility implements SingletonInterface{
	/**
	 * @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
	 */
	protected $tsParser;
	protected $tsParserTplRow;
	protected $tsParserConstants;
	protected $tsParserInitialized;

	/**
	 * @param $pid
	 * @param array $constants
	 * @param array $isSetConstants
	 */
	function applyToPid($pid,array $constants, $isSetConstants = array()) {
		$this->initializeTSParser($pid);
		$this->setConstants($pid, $constants, $isSetConstants);
		//@todo add hook to apply additional options
	}

	/**
	 * @param $pid
	 * @return mixed
	 */
	function getConstants($pid) {
		$this->initializeTSParser($pid);

		$return = $this->tsParserConstants;
		foreach($return as $key => $field) {

			$return[$key]['isDefault'] = ($field['default_value'] === $field['default_value']);

			if($field['type'] === 'int') {
				$return[$key]['typeCleaned'] = 'Int';
			} elseif($field['type'] === 'int+') {
				$return[$key]['typeCleaned'] = 'Int';
			} elseif($field['type'] === 'small') {
				$return[$key]['typeCleaned'] = 'Text';
			} elseif($field['type'] === 'color') {
				$return[$key]['typeCleaned'] = 'Color';
			} else {
				$return[$key]['typeCleaned'] = 'Fallback';
			}

		}

		return $return;
	}

	/**
	 * @param $pid
	 * @param array $categoriesToShow
	 * @param array $deniedFields
	 * @return array
	 */
	function getCategories($pid, $categoriesToShow = array(), $deniedFields = array()) {
		$this->initializeTSParser($pid);

		#print_r($this->tsParser->subCategories);
		#die();

		foreach($this->tsParser->categories as $categorieName => $categorie) {
			if((count($categoriesToShow) === 0) || (in_array($categorieName, $categoriesToShow))) {
				foreach($categorie as $constantName => $type) {
					if(in_array($constantName, $deniedFields)) {
						unset($this->tsParser->categories[$categorieName][$constantName]);
					}
				}
			} else {
				unset($this->tsParser->categories[$categorieName]);
			}

		}

		return $this->tsParser->categories;
	}

	/**
	 * @param $pid
	 * @return \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
	 */
	function getTsParser($pid) {
		$this->initializeTSParser($pid);
		return $this->tsParser;
	}

	/**
	 * @todo access check!
	 *
	 * @param $pid
	 * @param $constants
	 * @param array $isSetConstants
	 * @return void
	 */
	protected function setConstants($pid, $constants, $isSetConstants = array()) {
		$this->getConstants($pid);

		$filteredConstants = array();
		/*foreach($constants as $constant) {
			foreach($this->tsParserConstants as $allowedConstants) {
				if($constant['name'] == $allowedConstants['name']) {
					$filteredConstants[] = $constant;
					break;
				}
			}
		}*/
		$filteredConstants = $constants;

		$postData = array(
			'data' => $constants,
			'check'=> $isSetConstants,
		);

		$this->tsParser->changed = 0;
		//$this->tsParser->ext_dontCheckIssetValues = 1;
		$this->tsParser->ext_procesInput($postData, $_FILES, $this->tsParserConstants, $this->tsParserTplRow);

		if ($this->tsParser->changed) {
			// Set the data to be saved
			$saveId = $this->tsParserTplRow['uid'];
			$recData = array();
			$recData['sys_template'][$saveId]['constants'] = implode($this->tsParser->raw, chr(10));
			// Create new  tce-object
			$tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$tce->stripslashes_values = 0;

			// Initialize
			$user = clone $GLOBALS['BE_USER'];
			$user->user['admin'] = 1;
			$tce->start($recData, Array(), $user);
			$tce->admin = 1;
			// Saved the stuff
			$tce->process_datamap();
			// Clear the cache (note: currently only admin-users can clear the cache in tce_main.php)
			$tce->clear_cacheCmd('all');
			unset($user);
		}
	}

	/**
	 * @param $pageId
	 * @param int $template_uid
	 * @return bool
	 */
	protected function initializeTSParser($pageId, $template_uid = 0) {
		if(!$this->tsParserInitialized) {
			$this->tsParserInitialized = TRUE;
			$this->tsParser = GeneralUtility::makeInstance('TYPO3\\CMS\Core\\TypoScript\\ExtendedTemplateService');
			$this->tsParser->tt_track = 0; // Do not log time-performance information
			$this->tsParser->init();

			$this->tsParser->ext_localGfxPrefix = ExtensionManagementUtility::extPath('tstemplate_ceditor');
			$this->tsParser->ext_localWebGfxPrefix = $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::extRelPath('tstemplate_ceditor');

			$this->tsParserTplRow = $this->tsParser->ext_getFirstTemplate($pageId, $template_uid);

			if (is_array($this->tsParserTplRow)) {
				/**
				 * @var t3lib_pageSelect $sys_page
				 */
				$sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
				$rootLine = $sys_page->getRootLine($pageId);
				$this->tsParser->runThroughTemplates($rootLine, $template_uid); // This generates the constants/config + hierarchy info for the template.
				$this->tsParserConstants = $this->tsParser->generateConfig_constants(); // The editable constants are returned in an array.
				$this->tsParser->ext_categorizeEditableConstants($this->tsParserConstants); // The returned constants are sorted in categories, that goes into the $tmpl->categories array
				$this->tsParser->ext_regObjectPositions($this->tsParserTplRow['constants']);
				// This array will contain key=[expanded constantname], value=linenumber in template. (after edit_divider, if any)
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	}
}