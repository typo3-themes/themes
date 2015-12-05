<?php

namespace KayStrobach\Themes\Hooks;

use KayStrobach\Themes\Domain\Model\Theme;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hooks into the theme repo to load the list of themes
 */
class ThemesDomainRepositoryThemeRepositoryInitHook {

	/**
	 * @var array
	 * @todo find a more flexible solution
	 */
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

	/**
	 * hook function
	 *
	 * @param $params
	 * @param $pObj
	 *
	 * @return void
	 * @todo add a more explaining description why this hook is required
	 */
	public function init(&$params, $pObj) {
		// exclude extensions, which are not worth to check them
		$extensionsToCheck = array_diff(
			ExtensionManagementUtility::getLoadedExtensionListArray(),
			$this->ignoredExtensions,
			scandir(PATH_typo3 . 'sysext')
		);

		// check extensions, which are worth to check
		foreach ($extensionsToCheck as $extensionName) {
			$extPath = ExtensionManagementUtility::extPath($extensionName);
			if (file_exists($extPath . 'Meta/theme.yaml') && file_exists($extPath . 'Configuration/TypoScript/setup.txt')) {
				$pObj->add(new Theme($extensionName));
			}
		}
	}

}
