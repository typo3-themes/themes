<?php

/**
 * manipulate the sys_template table
 */
$tempColumn = array(
	'tx_themes_skin' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:themes',
		'displayCond' => array(
			'FIELD:root:REQ:true'
		),
		'config' => array(
			'type' => 'select',
			'renderType' => 'selectSingle',
			'size' => 1,
			'maxitems' => 1,
			'itemsProcFunc' => 'KayStrobach\\Themes\\Tca\\ThemeSelector->items',
			'showIconTable' => 1,
		)
	),
);

// Add the skin selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_template', $tempColumn);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_template', '--div--;Themes,tx_themes_skin');
