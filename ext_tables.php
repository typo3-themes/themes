<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE'))
	die('Access denied.');


/**
 * manipulate the tt_content table
 */
/**
 * @todo: add some translations
 */
	$tempColumn = array();
	$tempColumn[] = array(
		'tx_themes_variants' => array(
			'exclude' => 1,
			'label' => 'Variants',
			'config' => array(
				'type' => 'user',
				'userFunc' => 'KayStrobach\\Themes\\Tca\\ContentVariants->renderField',
			)
		),
	);
	ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumn);
	ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_variants', '', 'after:section_frame');

/**
 * manipulate the sys_template table
 */
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
			'KayStrobach.' . $_EXTKEY, 'web', // Main area
			'mod1', // Name of the module
			'', // Position of the module
			array(// Allowed controller action combinations
				'Editor' => 'index,update,showTheme,setTheme,showThemeDetails',
			), array(// Additional configuration
				'access' => 'user,group',
				'icon' => 'EXT:themes/ext_icon.png',
				'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
			)
		);
	}

	// Add the skin selector for backend users.
	ExtensionManagementUtility::addTCAcolumns('sys_template', $tempColumn);
	ExtensionManagementUtility::addToAllTCAtypes('sys_template', '--div--;Themes,tx_themes_skin');

/**
 * auto inject base TS
 */
	$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['themes']);
	if(array_key_exists('themesIndependent', $extensionConfiguration) && ($extensionConfiguration['themesIndependent'] === '1')) {
		ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/setup.txt">');
		ExtensionManagementUtility::addTypoScriptConstants('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/constants.txt">');
	} else {
		ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'themes');
	}
	unset($extensionConfiguration);

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
			'switch-off' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_orange.png',
			'switch-disable' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_grey.png',
			'switch-on' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_green.png',
			'overlay-theme' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/overlay_theme.png',
		),
		$_EXTKEY
	);
