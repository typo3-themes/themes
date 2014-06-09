<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Define the TCA for the access control calendar selector.
$tempColumn = array(
	'tx_themes_skin' => array(
		'exclude' => 1,
		'label' => 'Themes',
		'displayCond' => 'FIELD:root:REQ:true',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'KayStrobach\\Themes\\Tca\\ThemeSelector->display',
		)
	),
);

if (TYPO3_MODE === 'BE') {
	ExtensionUtility::registerModule(
		'KayStrobach.' . $_EXTKEY,
		'web',          // Main area
		'mod1',         // Name of the module
		'',             // Position of the module
		array(          // Allowed controller action combinations
			'Editor' => 'index,update,showTheme,setTheme',
		),
		array(          // Additional configuration
			'access'    => 'user,group',
			'icon'      => 'EXT:themes/ext_icon.png',
			'labels'    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
		)
	);
}


// Add the skin selector for backend users.
GeneralUtility::loadTCA('sys_template');
ExtensionManagementUtility::addTCAcolumns('sys_template', $tempColumn);
ExtensionManagementUtility::addToAllTCAtypes('sys_template', '--div--;Themes,tx_themes_skin');


// @todo respect independent switch
ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'themes');

ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/setup.txt">');
ExtensionManagementUtility::addTypoScriptConstants('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/constants.txt">');

/**
 * add themes overlay
 */

array_push($GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'], 'themefound');
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['themefound'] = 'extensions-themes-overlay-theme';

/**
 * add sprites
 */

\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
	array(
		'switch-off'     => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_orange.png',
		'switch-disable' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_grey.png',
		'switch-on'      => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_green.png',
		'overlay-theme'  => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/overlay_theme.png',
	),
	$_EXTKEY
);