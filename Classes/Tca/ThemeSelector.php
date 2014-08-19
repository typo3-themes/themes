<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class ThemeSelector {

	/**
	 * Displays the Theme selector as a TCEForm's userfunc. Handles display of
	 * skins and copying skins but leaves the saving to TCEmain.
	 *
	 * @param	array	$pa
	 * @param	object	$pObj
	 * @return	string
	 */
	public function display($pa, $pObj) {
		/**
		 * @var Tx_Themes_Domain_Repository_ThemeRepository $repository
		 */
		$repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

		$view->setFormat('html');
		$view->setTemplatePathAndFilename(
			GeneralUtility::getFileAbsFileName('EXT:themes/Resources/Private/Templates/ThemeSelector.html')
		);
		$view->assignMultiple(array(
			'formField' => array(
				'table' => $pa['table'],
				'row' => $pa['row'],
			),
			'selectedTheme' => $repository->findByUid($pa['row']['tx_themes_skin']),
			'selectableThemes' => $repository->findAll(),
		));
		return $view->render();
	}

}
