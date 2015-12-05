<?php

namespace KayStrobach\Themes\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TemplateModuleBodyPostProcessHook
 *
 * Hook to change the look of the template module
 *
 * @todo get it working again, is broken since 6.2
 * @package KayStrobach\Themes\Hooks
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
	 * @return void
	 */
	public function main(&$params, &$pObj) {
		/**
		 * @var $repository \KayStrobach\Themes\Domain\Repository\ThemeRepository
		 * @var $view \TYPO3\CMS\Fluid\View\StandaloneView
		 */

		$getSet = GeneralUtility::_GP('SET');

		if (($getSet['function'] === 'tx_tstemplateinfo' || !$getSet['function'])
			&& ($params['moduleTemplateFilename'] === 'templates/tstemplate.html')) {

			$repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

			$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

			$view->setFormat('html');
			$view->setTemplatePathAndFilename(
				GeneralUtility::getFileAbsFileName('EXT:themes/Resources/Private/Templates/TsTemplateThemeData.html')
			);
			$view->assignMultiple(array(
				'selectedTheme' => $repository->findByPageId(GeneralUtility::_GP('id')),
				'selectableThemes' => $repository->findAll(),
			));

			$params['markers']['CONTENT'] = str_replace('<table class="t3-table-info">', '<table class="t3-table-info">' . $view->render(), $params['markers']['CONTENT']);
			return;
		}
	}

}
