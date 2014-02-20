<?php

########################################################################
# Extension Manager/Repository config file for ext "templavoila_framework".
#
# Auto generated 25-08-2011 21:37
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Themes - The theme selector',
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
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:89:{s:9:"ChangeLog";s:4:"e36b";s:37:"class.tx_templavoilaframework_lib.php";s:4:"219f";s:42:"class.tx_templavoilaframework_pagelink.php";s:4:"097a";s:46:"Skinselector.php";s:4:"50e9";s:43:"class.tx_templavoilaframework_t3dimport.php";s:4:"50f4";s:22:"default_screenshot.gif";s:4:"3711";s:16:"ext_autoload.php";s:4:"4b2c";s:21:"ext_conf_template.txt";s:4:"c7c7";s:12:"ext_icon.gif";s:4:"b040";s:17:"ext_localconf.php";s:4:"b5eb";s:14:"ext_tables.php";s:4:"c0bf";s:14:"ext_tables.sql";s:4:"dc24";s:16:"locallang_db.xml";s:4:"e7df";s:20:"template_objects.t3d";s:4:"df32";s:30:"core_templates/css/backend.css";s:4:"189d";s:40:"core_templates/css/backend_typo3_4-3.css";s:4:"1e3f";s:49:"core_templates/datastructures/fce/1mod (fce).html";s:4:"b269";s:48:"core_templates/datastructures/fce/1mod (fce).xml";s:4:"7dea";s:49:"core_templates/datastructures/fce/2col (fce).html";s:4:"723d";s:48:"core_templates/datastructures/fce/2col (fce).xml";s:4:"93ef";s:49:"core_templates/datastructures/fce/2mod (fce).html";s:4:"bfb0";s:48:"core_templates/datastructures/fce/2mod (fce).xml";s:4:"0a2c";s:49:"core_templates/datastructures/fce/3col (fce).html";s:4:"0096";s:48:"core_templates/datastructures/fce/3col (fce).xml";s:4:"5548";s:49:"core_templates/datastructures/fce/3mod (fce).html";s:4:"501d";s:48:"core_templates/datastructures/fce/3mod (fce).xml";s:4:"ef7e";s:49:"core_templates/datastructures/fce/4col (fce).html";s:4:"d3aa";s:48:"core_templates/datastructures/fce/4col (fce).xml";s:4:"43d5";s:49:"core_templates/datastructures/fce/4mod (fce).html";s:4:"5dc6";s:48:"core_templates/datastructures/fce/4mod (fce).xml";s:4:"ae74";s:57:"core_templates/datastructures/fce/feature_image (fce).xml";s:4:"6b4e";s:57:"core_templates/datastructures/fce/html_wrapper (fce).html";s:4:"26ba";s:56:"core_templates/datastructures/fce/html_wrapper (fce).xml";s:4:"54b7";s:55:"core_templates/datastructures/fce/plain_image (fce).xml";s:4:"0301";s:49:"core_templates/datastructures/page/f1 (page).html";s:4:"e3bd";s:48:"core_templates/datastructures/page/f1 (page).xml";s:4:"bd38";s:49:"core_templates/datastructures/page/f2 (page).html";s:4:"7ad9";s:48:"core_templates/datastructures/page/f2 (page).xml";s:4:"1bed";s:49:"core_templates/datastructures/page/f3 (page).html";s:4:"e30a";s:48:"core_templates/datastructures/page/f3 (page).xml";s:4:"1304";s:42:"core_templates/fce/fce_2_column_group.html";s:4:"2003";s:42:"core_templates/fce/fce_3_column_group.html";s:4:"25e5";s:42:"core_templates/fce/fce_4_column_group.html";s:4:"e993";s:45:"core_templates/fce/fce_dual_module_group.html";s:4:"daf1";s:40:"core_templates/fce/fce_html_wrapper.html";s:4:"771f";s:45:"core_templates/fce/fce_quad_module_group.html";s:4:"309a";s:47:"core_templates/fce/fce_single_module_group.html";s:4:"63f9";s:47:"core_templates/fce/fce_triple_module_group.html";s:4:"6705";s:35:"core_templates/fce/plain_image.html";s:4:"add8";s:28:"core_templates/js/backend.js";s:4:"b7dd";s:25:"core_templates/js/core.js";s:4:"6d3c";s:37:"core_templates/js/jquery-1.4.2.min.js";s:4:"1009";s:34:"core_templates/pages/f1a_core.html";s:4:"2c0a";s:34:"core_templates/pages/f1b_core.html";s:4:"6c6f";s:34:"core_templates/pages/f1c_core.html";s:4:"e4b8";s:34:"core_templates/pages/f1d_core.html";s:4:"9889";s:34:"core_templates/pages/f1e_core.html";s:4:"8b14";s:34:"core_templates/pages/f1f_core.html";s:4:"6d8b";s:34:"core_templates/pages/f2a_core.html";s:4:"7697";s:34:"core_templates/pages/f2b_core.html";s:4:"3d81";s:34:"core_templates/pages/f2c_core.html";s:4:"8058";s:34:"core_templates/pages/f2d_core.html";s:4:"24d0";s:34:"core_templates/pages/f2e_core.html";s:4:"3713";s:34:"core_templates/pages/f3a_core.html";s:4:"29eb";s:34:"core_templates/pages/f3b_core.html";s:4:"c975";s:34:"core_templates/pages/f3c_core.html";s:4:"db81";s:34:"core_templates/pages/f3d_core.html";s:4:"a83e";s:48:"core_templates/thumbnails/_master_thumbnails.psd";s:4:"0c18";s:33:"core_templates/thumbnails/f1a.gif";s:4:"dc70";s:33:"core_templates/thumbnails/f1b.gif";s:4:"dc70";s:33:"core_templates/thumbnails/f1c.gif";s:4:"dc70";s:33:"core_templates/thumbnails/f1d.gif";s:4:"c81a";s:33:"core_templates/thumbnails/f1e.gif";s:4:"e8db";s:33:"core_templates/thumbnails/f1f.gif";s:4:"7be3";s:33:"core_templates/thumbnails/f2a.gif";s:4:"66b3";s:33:"core_templates/thumbnails/f2b.gif";s:4:"66b3";s:33:"core_templates/thumbnails/f2c.gif";s:4:"66b3";s:33:"core_templates/thumbnails/f2d.gif";s:4:"b718";s:33:"core_templates/thumbnails/f2e.gif";s:4:"9043";s:33:"core_templates/thumbnails/f3a.gif";s:4:"5313";s:33:"core_templates/thumbnails/f3b.gif";s:4:"5313";s:33:"core_templates/thumbnails/f3c.gif";s:4:"5313";s:33:"core_templates/thumbnails/f3d.gif";s:4:"5313";s:43:"core_templates/typoscript/core_constants.ts";s:4:"a0f1";s:44:"core_templates/typoscript/core_typoscript.ts";s:4:"56bd";s:14:"doc/manual.sxw";s:4:"876f";s:18:"lang/locallang.xml";s:4:"f9fe";s:38:"xclass/class.ux_t3lib_tsparser_ext.php";s:4:"0eb9";s:36:"xclass/class.ux_t3lib_tstemplate.php";s:4:"2dba";}',
);

?>