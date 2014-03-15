<?php

namespace KayStrobach\Themes\Hook;

use KayStrobach\Themes\Domain\Model\Theme;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class ThemesDomainRepositoryThemeRepositoryInitHook {
	protected $ignoredExtensions = array(
		'themes',
		'skinselector_content',
		'skinpreview',
		'templavoila',
		'piwik',
		'piwikintegration',
		'templavoila_framework',
		'be_acl',
		'sitemgr',
		'sitemgr_template',
		'sitemgr_fesettings',
		'sitemgr_fe_notfound',
		'cal',
		'extension_builder',
		'coreupdate',
		'contextswitcher',
		'extdeveval',
		'powermail',
		'kickstarter',
		'tt_news',
		'dyncss',
		'dyncss_less',
		'dyncss_scss',
		'dyncss_turbine',
		'static_info_tables',
		'realurl',
	);

	function init(&$params, $pObj) {
		// exclude extensions, which are not worth to check them
		$extensionsToCheck = array_diff(
			ExtensionManagementUtility::getLoadedExtensionListArray(),
			ExtensionManagementUtility::getRequiredExtensionListArray(),
			$this->ignoredExtensions,
			scandir(PATH_typo3 . 'sysext')
		);

		// check extensions, which are worth to check
		foreach($extensionsToCheck as $extensionName) {
			$extPath = ExtensionManagementUtility::extPath($extensionName);
			if(file_exists($extPath . 'Configuration/Meta/theme.yaml') && file_exists($extPath . 'Configuration/TypoScript/setup.ts')) {
				$pObj->add(new Theme($extensionName));
			}
		}
	}
}