<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

/*
 * Page TSconfig for the new content element wizard: Configuration/page.tsconfig, loaded by TYPO3 v12 and later
 */

/*
 * Register hook to inject themes
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\KayStrobach\Themes\Domain\Repository\ThemeRepository::class]['init'][]
    = \KayStrobach\Themes\Hooks\ThemesDomainRepositoryThemeRepositoryInitHook::class . '->init';

/*
 * register frontend plugin to allow usage of extbase controller
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Themes',
    'Theme',
    [\KayStrobach\Themes\Controller\ThemeController::class => 'index'],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
