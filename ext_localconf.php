<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/*
 * Add page typoscript for new content element wizard
 */
$tsconfig = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/PageTS/tsconfig.typoscript">';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($tsconfig);

/*
 * Register hook to inject themes
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository']['init'][]
    = 'KayStrobach\\Themes\\Hooks\\ThemesDomainRepositoryThemeRepositoryInitHook->init';

/*
 * register used hooks to inject the TS
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]
    = \KayStrobach\Themes\Hooks\T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook::class . '->main';

/**
 * register signal to inject pagets without xclassing
 * Requires signal slot call from http://forge.typo3.org/issues/59703.
 *
 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
 */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Backend\Utility\BackendUtility::class,
    'getPagesTSconfigPreInclude',
    \KayStrobach\Themes\Slots\BackendUtilitySlot::class,
    'getPagesTsConfigPreInclude'
);
unset($signalSlotDispatcher);

/*
 * register frontend plugin to allow usage of extbase controller
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'KayStrobach.themes',
    'Theme',
    ['Theme' => 'index'],
    []
);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1632667595] = [
    'nodeName' => 'ContentVariants',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentVariants::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1632667596] = [
    'nodeName' => 'ContentBehaviour',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentBehaviour::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1632667597] = [
    'nodeName' => 'ContentResponsive',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentResponsive::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1632667598] = [
    'nodeName' => 'ThemesContentColumnSettings',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentColumnSettings::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1632667599] = [
    'nodeName' => 'ThemesContentEnforceEqualColumnHeight',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentEnforceEqualColumnHeight::class
];
