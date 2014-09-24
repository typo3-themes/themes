<?php

########################################################################
# Extension Manager/Repository config file for ext "Themes".
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'THEMES - The theme engine',
	'description' => '',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Kay Strobach',
	'author_email' => 'typo3@kay-strobach.de',
	'author_company' => 'private',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '6.2.0-6.2.99',
			'static_info_tables' => '6.0.10-6.1.99',
			'yaml_parser' => '1.0.0-1.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
);

?>
