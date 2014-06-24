<?php

namespace KayStrobach\Themes\Domain\Model;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class Theme extends AbstractTheme {

	/**
	 * Constructs a new Theme
	 *
	 * @api
	 */
	public function __construct($extensionName) {

		parent::__construct($extensionName);

		if (ExtensionManagementUtility::isLoaded($extensionName, FALSE)) {
			// set needed path variables
			$path = ExtensionManagementUtility::extPath($this->getExtensionName());
			$this->pathTyposcript = $path . 'Configuration/TypoScript/setup.txt';
			$this->pathTyposcriptConstants = $path . 'Configuration/TypoScript/constants.txt';
			$this->pathTSConfig = $path . 'Configuration/PageTS/tsconfig.txt';

			$this->importExtEmConf();

			if (is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/Screens/screenshot.png')) {
				$this->previewImage = ExtensionManagementUtility::extRelPath($this->getExtensionName()) . 'Meta/Screens/screenshot.png';
			} else {
				$this->previewImage = ExtensionManagementUtility::extRelPath('themes') . 'Resources/Public/Images/screenshot.gif';
			}

			if (is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml')) {
				if (class_exists('\Symfony\Component\Yaml\Yaml')) {
					$this->information = \Symfony\Component\Yaml\Yaml::parse(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml');
				}
			}
		}
	}

	/**
	 * @todo missing docblock
	 */
	protected function importExtEmConf() {
		/**
		 * @var $EM_CONF array
		 * @var $_EXTKEY string
		 */
		$_EXTKEY = $this->extensionName;
		include(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'ext_emconf.php');
		$this->title = $EM_CONF[$this->getExtensionName()]['title'];
		$this->description = $EM_CONF[$this->getExtensionName()]['description'];

		$this->version = $EM_CONF[$this->getExtensionName()]['version'];

		$this->author['name'] = $EM_CONF[$this->getExtensionName()]['author'];
		$this->author['email'] = $EM_CONF[$this->getExtensionName()]['author_email'];
		$this->author['company'] = $EM_CONF[$this->getExtensionName()]['author_company'];
	}

	/**
	 * @todo miss the description of the function
	 * @return string
	 */
	public function getTSConfig() {
		if (file_exists($this->getTSConfigAbsPath()) && is_file($this->getTSConfigAbsPath())) {
			return file_get_contents($this->getTSConfigAbsPath());
		} else {
			return '';
		}
	}

	/**
	 * @todo missing docblock
	 */
	public function getRelativePath() {
		if (ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
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
			'constants' => @is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
			'config' => @is_file($this->getTypoScriptAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptAbsPath()) : '',
			'include_static' => '',
			'include_static_file' => '',
			'title' => 'themes:' . $this->getExtensionName(),
			'uid' => md5($this->getExtensionName())
		);

		// @todo resources Path / private Path
		$themeItem['constants'] .= LF . 'themes.resourcesPrivatePath = ' . $this->getRelativePath() . 'Resources/Private/';
		$themeItem['constants'] .= LF . 'themes.resourcesPublicPath = ' . $this->getRelativePath() . 'Resources/Public/';
		$themeItem['constants'] .= LF . 'themes.relativePath = ' . $this->getRelativePath();
		$themeItem['constants'] .= LF . 'themes.name = ' . $this->getExtensionName();
		$themeItem['constants'] .= LF . 'themes.templatePageId = ' . $params['pid'];
		$themeItem['constants'] .= LF . $this->getTypoScriptForLanguage($params, $pObj);

		$pObj->processTemplate(
			$themeItem,
			$params['idList'] . ',ext_theme' . str_replace('_', '', $this->getExtensionName()),
			$params['pid'], 'ext_theme' . str_replace('_', '', $this->getExtensionName()),
			$params['templateId']
		);
	}

}
