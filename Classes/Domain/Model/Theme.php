<?php

namespace KayStrobach\Themes\Domain\Model;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Theme extends AbstractTheme {
	/**
	 * Constructs a new Theme
	 *
	 * @api
	 */
	public function __construct($extensionName) {

		parent::__construct($extensionName);

		if(ExtensionManagementUtility::isLoaded($extensionName, FALSE)) {
			/**
			 * set needed path variables
			 */
			$path                          = ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Configuration/Theme/';
			$this->pathTyposcript          = $path . 'setup.ts';
			$this->pathTyposcriptConstants = $path . 'constants.ts';
			$this->pathTSConfig            = $path . 'tsconfig.ts';

			$this->importExtEmConf();

			if(is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.png')) {
				$this->previewImage      = ExtensionManagementUtility::extRelPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.png';
			} elseif (is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.gif')) {
				$this->previewImage      = ExtensionManagementUtility::extRelPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.gif';
			} else {
				$this->previewImage      = ExtensionManagementUtility::extRelPath('themes') . 'Resources/Public/Images/screenshot.gif';
			}

			if(is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml')) {
				if(class_exists('\Symfony\Component\Yaml\Yaml')) {
					$this->information = \Symfony\Component\Yaml\Yaml::parse(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml');
				}
			}
		}
	}

	protected function importExtEmConf() {
		/**
		 * @var $EM_CONF array
		 * @var $_EXTKEY string
		 */
		$_EXTKEY                 = $this->extensionName;
		include(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'ext_emconf.php');
		$this->title             = $EM_CONF[$this->getExtensionName()]['title'];
		$this->description       = $EM_CONF[$this->getExtensionName()]['description'];

		$this->version           = $EM_CONF[$this->getExtensionName()]['version'];

		$this->author['name']    = $EM_CONF[$this->getExtensionName()]['author'];
		$this->author['email']   = $EM_CONF[$this->getExtensionName()]['author_email'];
		$this->author['company'] = $EM_CONF[$this->getExtensionName()]['author_company'];

	}

	/**
	 * @return string
	 */
	public function getTSConfig() {
		if(file_exists($this->getTSConfigAbsPath()) && is_file($this->getTSConfigAbsPath())) {
			return file_get_contents($this->getTSConfigAbsPath());
		} else {
			return '';
		}
	}

	public function getRelativePath() {
		if(ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
			return ExtensionManagementUtility::siteRelPath($this->getExtensionName());
		} else {
			return '';
		}
	}

	/**
	 * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
	 *
	 * @param	array		Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
	 * @param	object		Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return	void
	 */
	public function addTypoScriptForFe(&$params, &$pObj) {
		$themeItem = array(
			'constants'          => @is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
			'config'             => @is_file($this->getTypoScriptAbsPath())          ? GeneralUtility::getUrl($this->getTypoScriptAbsPath()) : '',
			'editorcfg'          => '',
			'include_static'     => '',
			'include_static_file'=>	'',
			'title' =>	'themes:' . $this->getExtensionName(),
			'uid' => md5($this->getExtensionName())
		);

		// @todo resources Path / private Path
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.resourcesPrivatePath = ' . $this->getRelativePath() . 'Resources/Private/';
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.resourcesPublicPath  = ' . $this->getRelativePath() . 'Resources/Public/';
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.relativePath         = ' . $this->getRelativePath();
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.name                 = ' . $this->getExtensionName();
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.templatePageId       = ' . $params['pid'];

		$pObj->processTemplate(
			$themeItem,
			$params['idList'] . ',themes_' . $this->getExtensionName(),
			$params['pid'],
			'themes_' . $this->getExtensionName(),
			$params['templateId']
		);
	}
}
