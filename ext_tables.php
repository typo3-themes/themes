<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
$extensionConfiguration = $configurationUtility->getCurrentConfiguration('themes');

/* allow inclusion of static typoscript file */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Themes');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/FluidStyledContent', 'Themes (Experimental: Additional add this for using fluid_styled_content)');

unset($extensionConfiguration);


if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'KayStrobach.'.$_EXTKEY, 'web', // Main area
        'mod1', // Name of the module
        '', // Position of the module
        [// Allowed controller action combinations
            'Editor' => 'index,update,showTheme,setTheme,showThemeDetails,saveCategoriesFilterSettings',
        ], [// Additional configuration
            'access'         => 'user,group',
            'iconIdentifier' => 'module-themes',
            'labels'         => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xml',
        ]
    );
    // Add some backend stylesheets and javascript
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
        = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Hooks/PageRenderer.php:KayStrobach\\Themes\\Hooks\\PageRenderer->addJSCSS';
}

/*
 * add themes overlay
 */
if (!isset($GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'])) {
    $GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'] = array();
}
array_push($GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'], 'themefound');
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['themefound'] = 'extensions-themes-overlay-theme';


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_themes_buttoncontent');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:themes/Resources/Private/Language/ButtonContent.xlf:tt_content.CType_pi1',
        $_EXTKEY.'_buttoncontent_pi1',
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($_EXTKEY).'Resources/Public/Icons/button_content.svg',
    ],
    'CType'
);


// register svg icons: identifier and filename
$iconsSvg = [
    'module-themes' => 'ext_icon.svg',
    'content-button' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
    'switch-off' => 'Resources/Public/Icons/power_grey.svg',
    'switch-on' => 'Resources/Public/Icons/power_green.svg',
    'switch-disable' => 'Resources/Public/Icons/power_orange.svg',
    'overlay-theme' => 'Resources/Public/Icons/overlay_theme.svg',
    'contains-theme' => 'ext_icon.png',
    'new_content_el_buttoncontent' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($iconsSvg as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:' . $_EXTKEY . '/' . $path]
    );
}


$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] =
    [
        0 => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:contains-theme',
        1 => 'themes',
        2 => 'extensions-themes-contains-theme',
    ];

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-themes'] = 'extensions-themes-contains-theme';
