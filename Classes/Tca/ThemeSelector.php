<?php

/**
 *
 */

class Tx_Themes_Tca_ThemeSelector {

	/**
	 * Displays the Theme selector as a TCEForm's userfunc. Handles display of
	 * skins and copying skins but leaves the saving to TCEmain.
	 *
	 * @param	array	$PA
	 * @param	object	$pObj
	 * @return	string
	 */
	public function display($PA, $pObj) {
		$repository = t3lib_div::makeInstance('Tx_Themes_Domain_Repository_ThemeRepository');

		$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');

		$view->setFormat('html');
		$view->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:themes/Resources/Private/Templates/ThemeSelector.html'));
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

