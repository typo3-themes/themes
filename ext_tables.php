<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Add some backend stylesheets and javascript
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
    = \KayStrobach\Themes\Hooks\PageRenderer::class . '->addJSCSS';

/*
 * add themes overlay
 */
if (!isset($GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'])) {
    $GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'] = [];
}
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'][] = 'themefound';
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['themefound'] = 'extensions-themes-overlay-theme';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_themes_buttoncontent');

// register svg icons: identifier and filename
$iconsSvg = [
    'module-themes' => 'Resources/Public/Icons/Extension.svg',
    'content-button' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
    'switch-off' => 'Resources/Public/Icons/power_grey.svg',
    'switch-on' => 'Resources/Public/Icons/power_green.svg',
    'switch-disable' => 'Resources/Public/Icons/power_orange.svg',
    'overlay-theme' => 'Resources/Public/Icons/overlay_theme.svg',
    'contains-theme' => 'Resources/Public/Icons/Extension.svg',
    'new_content_el_buttoncontent' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($iconsSvg as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:themes/' . $path]
    );
}
