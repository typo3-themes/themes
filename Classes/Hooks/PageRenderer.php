<?php
namespace KayStrobach\Themes\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
* Class/Function which adds the necessary ExtJS and pure JS stuff for themes.
*
* @author Thomas Deuling <typo3@coding.ms>
* @package TYPO3
* @subpackage tx_themes
*/
class PageRenderer {

	/**
	* wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess)
	*
	* @param array $parameters : An array of available parameters
	* @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer : The parent object that triggered this hook
	*
	* @return void
	*/
	public function addJSCSS($parameters, &$pageRenderer) {
		// Add javascript
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/ThemesBackendTca');
		// Add css
		$filename = $pageRenderer->backPath . ExtensionManagementUtility::extRelPath('themes') . 'Resources/Public/Stylesheet/ThemesBackendTca.css';
		$pageRenderer->addCssFile($filename, 'stylesheet', 'screen');
	}
}