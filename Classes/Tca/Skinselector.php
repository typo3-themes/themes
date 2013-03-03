<?php

/**
 *
 */

class Tx_Themes_Tca_Skinselector {

	/**
	 * Displays the skin selector as a TCEForm's userfunc. Handles display of
	 * skins and copying skins but leaves the saving to TCEmain.
	 *
	 * @param	array	$PA
	 * @param	object	$pObj
	 * @return	string
	 */
	public function display($PA, $pObj) {
		$repository = t3lib_div::makeInstance('Tx_Themes_Domain_Repository_SkinRepository');

		$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');

		$view->setFormat('html');
		$view->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:themes/Resources/Private/Templates/Skinselector.html'));
		$view->assignMultiple(array(
			'formField' => array(
				'table' => $PA['table'],
				'row'   => $PA['row'],
			),
			'selectedSkin' => $repository->findByUid($PA['row']['tx_themes_skin']),
			'selectableSkins' => $repository->findAll(),
		));
		return $view->render();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['EXT:themes/Classes/Tca/Skinselector.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['EXT:themes/Classes/Tca/Skinselector.php']);
}

?>
