<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * register used hooks
 */

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]
	= 'Tx_Themes_T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook->main';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][]
	= 'Tx_Themes_TemplateModuleBodyPostProcessHook->main';

/**
 * Register Xclasses the old way for prior 6.0
 */
$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tsparser_tsconfig.php'] = t3lib_extMgm::extPath('themes') . 'Classes/XClass/Ux_T3lib_TSparser_TSconfig.php';
