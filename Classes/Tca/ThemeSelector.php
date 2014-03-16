<?php

namespace KayStrobach\Themes\Tca;

/**
 *
 */

class ThemeSelector {

	/**
	 * Displays the Theme selector as a TCEForm's userfunc. Handles display of
	 * skins and copying skins but leaves the saving to TCEmain.
	 *
	 * @param	array	$PA
	 * @param	object	$pObj
	 * @return	string
	 */
	public function display($PA, $pObj) {
		/**
		 * @var Tx_Themes_Domain_Repository_ThemeRepository $repository
		 */
		$repository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Model\\ThemeRepository');

		$view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Fluid_View_StandaloneView');

		$view->setFormat('html');
		$view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:themes/Resources/Private/Templates/ThemeSelector.html'));
		$view->assignMultiple(array(
			'formField' => array(
				'table' => $PA['table'],
				'row'   => $PA['row'],
			),
			'selectedTheme' => $repository->findByUid($PA['row']['tx_themes_skin']),
			'selectableThemes' => $repository->findAll(),
		));
		return $view->render();
	}

}

