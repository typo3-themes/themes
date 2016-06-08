<?php

/**
 * manipulate the tt_content table
 */
$tempColumn = array(
	'tx_themes_buttoncontent' => array(
		'exclude' => 1,
		'label'   => 'LLL:EXT:themes_gridelements/Resources/Private/Language/ButtonContent.xlf:tx_themes_buttoncontent',
		'config'  => array(
			'type'           => 'inline',
			'appearance'     => array(
				'levelLinksPosition'              => 'top',
				'showPossibleLocalizationRecords' => TRUE,
				'showRemovedLocalizationRecords'  => TRUE,
				'showAllLocalizationLink'         => TRUE,
				'showSynchronizationLink'         => TRUE,
				'enabledControls'                 => array(
					'info'     => TRUE,
					'new'      => TRUE,
					'dragdrop' => TRUE,
					'sort'     => TRUE,
					'hide'     => TRUE,
					'delete'   => TRUE,
					'localize' => TRUE,
				)
			),
			'inline'         => array(
				'inlineNewButtonStyle' => 'display: inline-block;',
			),
			'behaviour'      => array(
				'localizationMode'                     => 'select',
				'localizeChildrenAtParentLocalization' => TRUE,
			),
			'foreign_table'  => 'tx_themes_buttoncontent',
			'foreign_field'  => 'tt_content',
			'foreign_sortby' => 'sorting',
			'size'           => 5,
			'autoSizeMax'    => 20,
		)
	),
	'tx_themes_icon' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:icon',
		'config' => array(
			'type' => 'select',
			'selicon_cols' => 14,
			'items' => array(
				array('', '')
			),
		)
	),
	'tx_themes_variants' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'KayStrobach\\Themes\\Tca\\ContentVariants->renderField',
		)
	),
	'tx_themes_behaviour' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:behaviour',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'KayStrobach\\Themes\\Tca\\ContentBehaviour->renderField',
		)
	),
	'tx_themes_responsive' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:responsive_settings',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'KayStrobach\\Themes\\Tca\\ContentResponsive->renderField',
		)
	),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumn);

$GLOBALS['TCA']['tt_content']['types']['themes_buttoncontent_pi1']['showitem'] = '--palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.general;general, header, header_link, tx_themes_buttoncontent, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.appearance, --palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.frames;frames, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, --palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.visibility;visibility, --palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.access;access, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.extended, --div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category, categories';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_icon', '', 'after:header');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_variants', '', 'after:layout');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_behaviour', '', 'after:tx_themes_variants');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_themes_responsive', '', 'after:tx_themes_behaviour');
