<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * Register hook to inject themes
 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init'][]
		= 'KayStrobach\\Themes\\Hook\\ThemesDomainRepositoryThemeRepositoryInitHook->init';

/**
 * Register a page not found handler, overwrites the one in the install tool
 */

	$GLOBALS['TYPO3_CONF_VARS']['FE']['pageUnavailable_handling']
		= 'USER_FUNCTION:KayStrobach\\Themes\\Hook\\PageNotFoundHandlingHook->main';

/**
 * register used hooks to inject the TS
 */

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]
		= 'KayStrobach\\Themes\\Hook\\T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook->main';

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][]
		= 'KayStrobach\\Themes\\Hook\\TemplateModuleBodyPostProcessHook->main';

/**
 * Register XClasses to be a bit compatible to older versions
 */

	if(version_compare(TYPO3_version, '6.2.0', '<')) {
		/**
		 * Register Xclasses the new way from 6.0
		 * @todo test
		 */
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Configuration\\TsConfigParser'] = array(
			'className' => 'KayStrobach\\Themes\\XClass\\TsConfigParser',
		);
	}

/**
 *
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideIconOverlay'][]
		= 'KayStrobach\\Themes\\Hook\\IconUtilityHook->overrideIconOverlay';
 */
