<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:themes/Resources/Private/Language/ButtonContent.xlf:tt_content.CType_pi1',
        'themes_buttoncontent_pi1',
        'EXT:themes/Resources/Public/Icons/button_content.svg',
    ],
    'CType',
    'themes'
);

/**
 * manipulate the tt_content table.
 */
$tempColumn = [
    'tx_themes_buttoncontent' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:themes_gridelements/Resources/Private/Language/ButtonContent.xlf:tx_themes_buttoncontent',
        'config'  => [
            'type'           => 'inline',
            'appearance'     => [
                'levelLinksPosition'              => 'top',
                'showPossibleLocalizationRecords' => true,
                'showRemovedLocalizationRecords'  => true,
                'showAllLocalizationLink'         => true,
                'showSynchronizationLink'         => true,
                'enabledControls'                 => [
                    'info'     => true,
                    'new'      => true,
                    'dragdrop' => true,
                    'sort'     => true,
                    'hide'     => true,
                    'delete'   => true,
                    'localize' => true,
                ],
            ],
            'inline'         => [
                'inlineNewButtonStyle' => 'display: inline-block;',
            ],
            'behaviour'      => [
                'allowLanguageSynchronization'         => true,
            ],
            'foreign_table'  => 'tx_themes_buttoncontent',
            'foreign_field'  => 'tt_content',
            'foreign_sortby' => 'sorting',
            'size'           => 5,
            'autoSizeMax'    => 20,
        ],
    ],
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
    'tx_themes_variants' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants',
        'config'  => [
            'type'       => 'input',
            'renderType' => 'ContentVariants',
        ],
    ],
    'tx_themes_behaviour' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:behaviour',
        'config'  => [
            'type'       => 'input',
            'renderType' => 'ContentBehaviour',
        ],
    ],
    'tx_themes_responsive' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:responsive_settings',
        'config'  => [
            'type'       => 'input',
            'renderType' => 'ContentResponsive',
        ],
    ],
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumn);

$GLOBALS['TCA']['tt_content']['types']['themes_buttoncontent_pi1']['showitem'] = '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general, header, header_link, tx_themes_buttoncontent, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended, --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_icon', '', 'after:header');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_variants', '', 'after:layout');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_behaviour', '', 'after:tx_themes_variants');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_responsive', '', 'after:tx_themes_behaviour');
