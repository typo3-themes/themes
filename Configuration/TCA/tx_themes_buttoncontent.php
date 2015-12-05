<?php

$l10n = 'LLL:EXT:themes/Resources/Private/Language/ButtonContent.xlf:';

return array(
	'ctrl'      => array(
		'title'              => $l10n . 'tx_themes_buttoncontent',
		'label'              => 'linktext',
		'tstamp'             => 'tstamp',
		'crdate'             => 'crdate',
		'cruser_id'          => 'cruser_id',
		'versioningWS'       => TRUE,
		'origUid'            => 't3_origuid',
		'sortby'             => 'sorting',
		'delete'             => 'deleted',
		'rootLevel'          => -1,
		'thumbnail'          => 'resources',
		'dividers2tabs'      => TRUE,
		'enablecolumns'      => array(
			'disabled' => 'hidden',
		),
		'iconfile'           => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('themes') . 'Resources/Public/Icons/new_content_el_ButtonContent.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'linktext,linktarget,linktitle,icon'
	),
	'columns' => array(
		'linktext' => array(
			'label'  => $l10n . 'linktext',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'max'  => 30,
			)
		),
		'linktarget' => array(
			'exclude' => 1,
			'label'   => $l10n . 'linktarget',
			'config'  => array(
				'eval' => trim,
				'max'  => 1024,
				'size' => 50,
				'softref' => 'typolink',
				'type' => 'input',
				'wizards' => array(
					'link' => array(
						'icon' => 'link_popup.gif',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
						'module' => array(
							'name' => 'wizard_element_browser',
							'urlParameters' => array(
								'mode' => 'wizard'
							),
						),
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
						'type' => 'popup',
					),
					'_PADDING' => 2,
				)
			)
		),
		'linktitle' => array(
			'exclude' => 1,
			'label'   => $l10n . 'linktitle',
			'config'  => array(
				'type' => 'input',
				'size' => 50,
				'max'  => 256,
				'eval' => 'required'
			)
		),
		'icon' => array(
			'exclude' => 1,
			'label'   => $l10n . 'icon',
			'config' => array(
				'type' => 'select',
				'selicon_cols' => 14,
				'items' => array(
					array('', '')
				)
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'items' => array(
					'1' => array(
						'0' => 'LLL:EXT:cms/locallang_ttc.xlf:hidden.I.0'
					)
				)
			)
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'default' => 0
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly'
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'default' => 0,
				'range' => array(
					'upper' => mktime(0, 0, 0, 12, 31, 2020)
				)
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly'
		),
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
						-1
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.default_value',
						0
					)
				)
			)
		),
		'l10n_parent' => Array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array(
				'type' => 'passthrough'
			)
		),
		'l10n_diffsource' => Array(
			'config' => array(
				'type' => 'passthrough'
			)
		),
		'fe_group' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
			'config' => array(
				'type' => 'select',
				'size' => 5,
				'maxitems' => 20,
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.usergroups', '--div--',),
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
			),
		),
		'tt_content' => Array(
			'config' => array(
				'type' => 'passthrough'
			)
		),
	),
	'types'     => array(
		'1' => array(
			'showitem' => 'linktext,linktarget,linktitle,icon,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,hidden,starttime,endtime,fe_group'
		),
	),

);