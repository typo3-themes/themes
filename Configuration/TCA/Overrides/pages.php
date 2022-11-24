<?php

/**
 * manipulate the pages table.
 */
$tempColumn = [
    'tx_themes_icon' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:icon',
        'config'  => [
            'type'         => 'select',
            'renderType'   => 'selectSingle',
            'items'        => [
                ['', ''],
            ],
        ],
    ],
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumn);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_themes_icon', '', 'after:layout');

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    0 => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:contains-theme',
    1 => 'themes',
    2 => 'extensions-themes-contains-theme',
];

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-themes'] = 'extensions-themes-contains-theme';
