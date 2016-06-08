<?php

if (!defined('TYPO3_MODE'))
	die('Access denied.');

/**
 * Add page typoscript for new content element wizard
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTS/tsconfig.txt">');

/**
 * Register hook to inject themes
 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init'][]
		= 'KayStrobach\\Themes\\Hooks\\ThemesDomainRepositoryThemeRepositoryInitHook->init';

/**
 * register used hooks to inject the TS
 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]
		= 'KayStrobach\\Themes\\Hooks\\T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook->main';

/**
 * register hook to manipulate the template module
 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][]
		= 'KayStrobach\\Themes\\Hooks\\TemplateModuleBodyPostProcessHook->main';

/**
 * register signal to inject pagets without xclassing
 * Requires signal slot call from http://forge.typo3.org/issues/59703
 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
 */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Backend\\Utility\\BackendUtility',
		'getPagesTSconfigPreInclude',
		'KayStrobach\\Themes\\Slots\\BackendUtilitySlot',
		'getPagesTsConfigPreInclude'
	);
	unset($signalSlotDispatcher);

/**
 * register hook to inject BeLayoutTsprovider
 */
	if (TYPO3_MODE === 'BE') {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['pagets']
			= 'KayStrobach\\Themes\\Provider\\PageTsBackendLayoutDataProvider';
	}

/**
 * register command controller
 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'KayStrobach\Themes\Command\ThemesCommandController';

/**
 * register frontend plugin to allow usage of extbase controller
 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
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

/**
 * Get YAML parser
 */
if(!class_exists('\Symfony\Component\Yaml\Parser')) {
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('themes') . '/Resources/Private/PHP/vendor/autoload.php');
}