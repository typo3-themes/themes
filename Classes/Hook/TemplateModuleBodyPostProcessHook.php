<?php

namespace KayStrobach\Themes\Hook;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Missing description
 *
 * @todo missing docblock
 */
class TemplateModuleBodyPostProcessHook {

	/**
	 * $params = array(
	 *     'moduleTemplateFilename' => &$this->moduleTemplateFilename,
	 *     'moduleTemplate' => &$this->moduleTemplate,
	 *     'moduleBody' => &$moduleBody,
	 *     'markers' => &$markerArray,
	 *     'parentObject' => &$this
	 * );
	 *
	 * @param $params
	 * @param $pObj
	 * @internal param $this
	 */
	function main(&$params, &$pObj) {
		/**
		 * @var $repository \KayStrobach\Themes\Domain\Repository\ThemeRepository
		 * @var $view TYPO3\\CMS\\Fluid\\View\\StandaloneView
		 *
		 * @todo replace $_GET with GeneralUtility::_GP()
		 */
		if (($_GET['SET']['function'] === 'tx_tstemplateinfo' || !$_GET['SET']['function']) && ($params['moduleTemplateFilename'] === 'templates/tstemplate.html')) {

			$repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

			$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

			$view->setFormat('html');
			$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:themes/Resources/Private/Templates/TsTemplateThemeData.html'));
			$view->assignMultiple(array(
				'selectedTheme' => $repository->findByPageId(GeneralUtility::_GP('id')),
				'selectableThemes' => $repository->findAll(),
			));

			$params['markers']['CONTENT'] = str_replace('<table class="t3-table-info">', '<table class="t3-table-info">' . $view->render(), $params['markers']['CONTENT']);
			return;
		}
	}

}
