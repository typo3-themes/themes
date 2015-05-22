<?php

/**
 * manipulate the pages table
 */
$tempColumn = array(
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
	)
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumn);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_themes_icon', '', 'after:layout');


