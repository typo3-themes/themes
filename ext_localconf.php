<?php

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE'))
	die('Access denied.');

/**
 * Register hook to inject themes
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init'][] = 'KayStrobach\\Themes\\Hook\\ThemesDomainRepositoryThemeRepositoryInitHook->init';

/**
 * Register a page not found handler, overwrites the one in the install tool
 */
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageUnavailable_handling'] = 'USER_FUNCTION:KayStrobach\\Themes\\Hook\\PageNotFoundHandlingHook->main';

/**
 * register used hooks to inject the TS
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][] = 'KayStrobach\\Themes\\Hook\\T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook->main';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][] = 'KayStrobach\\Themes\\Hook\\TemplateModuleBodyPostProcessHook->main';

// Requires signal slot call from http://forge.typo3.org/issues/59703
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getPagesTSconfigPreInclude', 'KayStrobach\\Themes\\Slots\\BackendUtilitySlot', 'getPagesTSconfigPreInclude');
unset($signalSlotDispatcher);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideIconOverlay'][] = 'KayStrobach\\Themes\\Hook\\IconUtilityHook';

ExtensionUtility::configurePlugin(
	'KayStrobach.' . $_EXTKEY,
	'Theme', array(
		'Theme' => 'index'
	),
	array()
);


/**
 * register cache for extension
 */
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['themes_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['themes_cache'] = array();
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['themes_cache']['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['themes_cache']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend';
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['themes_cache']['options']['compression'] = 1;
}
