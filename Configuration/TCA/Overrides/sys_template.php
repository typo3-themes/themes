<?php

/**
 * manipulate the sys_template table.
 */
$tempColumn = [
    'tx_themes_skin' => [
        'exclude'     => 1,
        'label'       => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:themes',
        'displayCond' => 'FIELD:root:REQ:true',
        'config' => [
            'type'          => 'select',
            'renderType'    => 'selectSingle',
            'size'          => 1,
            'maxitems'      => 1,
            'itemsProcFunc' => 'KayStrobach\\Themes\\Tca\\ThemeSelector->items',
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false
                ]
            ]
        ],
    ],
    'tx_themes_extensions' => [
        'label'       => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:theme_extensions',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'size' => 10,
            'maxitems' => 100,
            'items' => [],
        ]
    ],
    'tx_themes_features' => [
        'label'       => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:theme_features',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'size' => 10,
            'maxitems' => 100,
            'items' => [],
        ]
    ],
];

// Add the skin selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_template', $tempColumn);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_template',
    '--div--;Themes,tx_themes_skin,tx_themes_extensions,tx_themes_features'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'themes',
    'Configuration/TypoScript',
    'Themes'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'themes',
    'Configuration/TypoScript/FluidStyledContent',
    'Themes (For backward compatibility: Additional add this for using fluid_styled_content)'
);
