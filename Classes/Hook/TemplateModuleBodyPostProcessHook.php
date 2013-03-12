<?php

class Tx_Themes_TemplateModuleBodyPostProcessHook {
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
		 * @var $repository Tx_Themes_Domain_Repository_ThemeRepository
		 * @var $view       Tx_Fluid_View_StandaloneView
		 */
		if(($_GET['SET']['function'] === 'tx_tstemplateinfo' || !$_GET['SET']['function'])
			&& ($params['moduleTemplateFilename'] === 'templates/tstemplate.html')) {

			$repository = t3lib_div::makeInstance('Tx_Themes_Domain_Repository_ThemeRepository');

			$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');

			$view->setFormat('html');
			$view->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:themes/Resources/Private/Templates/TsTemplateThemeData.html'));
			$view->assignMultiple(array(
				'selectedTheme' => $repository->findByPageId(t3lib_div::_GP('id')),
				'selectableThemes' => $repository->findAll(),
			));

			$params['markers']['CONTENT'] = str_replace('<table class="t3-table-info">', '<table class="t3-table-info">' . $view->render(), $params['markers']['CONTENT']);
			return;

			$headerEnd = strpos($params['markers']['CONTENT'], '</h2>');

			if($headerEnd) {
				$params['markers']['CONTENT'] = substr($params['markers']['CONTENT'], 0, $headerEnd + 5)
					. $view->render()
					. substr($params['markers']['CONTENT'], $headerEnd + 6);
			} else {
				$params['markers']['CONTENT'] = $view->render() . $params['markers']['CONTENT'];
			}
		}
	}
}