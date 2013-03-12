<?php

// class for: $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]

class Tx_Themes_T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook {
	/**
	 * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
	 *
	 * @param	array		Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
	 * @param	object		Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return	void
	 */
	public static function main(&$params, &$pObj) {
		$idList = $params['idList'];
		$templateID = $params['templateId'];
		$pid = $params['pid'];
		$row = $params['row'];

		// Call hook for possible manipulation of current skin.
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/templavoila_framework/class.tx_templavoilaframework_lib.php']['assignSkinKey'])) {
			$_params = array('skinKey' => &$row['tx_themes_skin']);
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/templavoila_framework/class.tx_templavoilaframework_lib.php']['assignSkinKey'] as $userFunc) {
				$row['tx_themes_skin'] = t3lib_div::callUserFunction($userFunc, $_params, $ref = NULL);
			}
		}

		/**
		 * @var $themeRepository Tx_Skinselector_Domain_Repository_SkinRepository
		 */
		$themeRepository = t3lib_div::makeInstance('Tx_Themes_Domain_Repository_ThemeRepository');
		$theme = $themeRepository->findByUid($row['tx_themes_skin']);
		if($theme !== NULL) {
			$theme->addTypoScriptForFe($params, $pObj);
		}
	}
}