<?php

if (!defined('TYPO3')) {
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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\KayStrobach\Themes\Domain\Repository\ThemeRepository::class]['init'][]
    = \KayStrobach\Themes\Hooks\ThemesDomainRepositoryThemeRepositoryInitHook::class . '->init';

/*
 * register used hooks to inject the TS
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]
    = \KayStrobach\Themes\Hooks\T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook::class . '->main';

/*
 * register frontend plugin to allow usage of extbase controller
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Themes',
    'Theme',
    [\KayStrobach\Themes\Controller\ThemeController::class => 'index'],
    []
);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1_632_667_595] = [
    'nodeName' => 'ContentVariants',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentVariants::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1_632_667_596] = [
    'nodeName' => 'ContentBehaviour',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentBehaviour::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1_632_667_597] = [
    'nodeName' => 'ContentResponsive',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentResponsive::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1_632_667_598] = [
    'nodeName' => 'ThemesContentColumnSettings',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentColumnSettings::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1_632_667_599] = [
    'nodeName' => 'ThemesContentEnforceEqualColumnHeight',
    'priority' => '70',
    'class' => \KayStrobach\Themes\Tca\ContentEnforceEqualColumnHeight::class
];
