<?php

if (!defined('TYPO3_MODE'))
	die('Access denied.');


/**
 * auto inject base TS
 */

/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
$extensionConfiguration = $configurationUtility->getCurrentConfiguration('themes');

if ((is_array($extensionConfiguration))
	&& (array_key_exists('themesIndependent', $extensionConfiguration))
	&& ($extensionConfiguration['themesIndependent'] === '1')) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/setup.txt">');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:themes/Configuration/TypoScript/constants.txt">');
} else {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Themes');
}
unset($extensionConfiguration);


if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'KayStrobach.' . $_EXTKEY, 'web', // Main area
		'mod1', // Name of the module
		'', // Position of the module
		array(// Allowed controller action combinations
			'Editor' => 'index,update,showTheme,setTheme,showThemeDetails,saveCategoriesFilterSettings',
		), array(// Additional configuration
			'access' => 'user,group',
			'icon' => 'EXT:themes/ext_icon.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
		)
	);
	// Add some backend stylesheets and javascript
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] 
		= \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Hooks/PageRenderer.php:KayStrobach\\Themes\\Hooks\\PageRenderer->addJSCSS';
}

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
			'switch-off' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_orange.png',
			'switch-disable' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_grey.png',
			'switch-on' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/power_green.png',
			'overlay-theme' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/overlay_theme.png',
		),
		$_EXTKEY
	);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_themes_buttoncontent');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	array(
		'LLL:EXT:themes/Resources/Private/Language/ButtonContent.xlf:tt_content.CType_pi1',
		$_EXTKEY . '_buttoncontent_pi1',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'buttoncontent_icon.gif'
	),
	'CType'
);
