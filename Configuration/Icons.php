<?php

/*
 * Icons of EXT:themes, TYPO3 v12 and later load this file themselves.
 */
$icons = [];
foreach ([
    'module-themes' => 'Resources/Public/Icons/Extension.svg',
    'content-button' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
    'switch-off' => 'Resources/Public/Icons/power_grey.svg',
    'switch-on' => 'Resources/Public/Icons/power_green.svg',
    'switch-disable' => 'Resources/Public/Icons/power_orange.svg',
    'overlay-theme' => 'Resources/Public/Icons/overlay_theme.svg',
    'contains-theme' => 'Resources/Public/Icons/Extension.svg',
    'new_content_el_buttoncontent' => 'Resources/Public/Icons/new_content_el_ButtonContent.svg',
] as $identifier => $path) {
    $icons[$identifier] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:themes/' . $path,
    ];
}
return $icons;
