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
