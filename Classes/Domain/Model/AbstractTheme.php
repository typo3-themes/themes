<?php

namespace KayStrobach\Themes\Domain\Model;

use KayStrobach\Themes\Utilities\ApplicationContext;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractTheme
 *
 * @package KayStrobach\Themes\Domain\Model
 * @todo get rid of getExtensionname, use EXT:extname as theme name to avoid conflicts in the database
 */
class AbstractTheme extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $author = array();

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $extensionName;

	/**
	 * @var string
	 */
	protected $version = '';

	/**
	 * @var string
	 */
	protected $previewImage;

	/**
	 * @var string
	 */
	protected $pathTyposcript;

	/**
	 * @var string
	 */
	protected $pathTyposcriptConstants;

	/**
	 * @var string
	 */
	protected $pathTsConfig;

	/**
	 * @var array
	 */
	protected $metaInformation = array();

	/**
	 * Constructs a new Theme
	 *
	 * @api
	 */
	public function __construct($extensionName) {
		$this->extensionName = $extensionName;
	}

	/**
	 * Returns the title
	 *
	 * @return string
	 * @api
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 * @api
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns the previewImage
	 *
	 * @return string
	 * @api
	 */
	public function getPreviewImage() {
		return $this->previewImage;
	}

	/**
	 * @return array
	 */
	public function getAllPreviewImages() {
		return array(
			array(
				'file'    => $this->getPreviewImage(),
				'caption' => '',
			)
		);
	}

	/**
	 * Returns the previewImage
	 *
	 * @return string
	 * @api
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

	/**
	 * @return array
	 */
	public function getMetaInformation() {
		return $this->metaInformation;
	}

	/**
	 * @param array $metaInformation
	 */
	public function setMetaInformation($metaInformation) {
		$this->metaInformation = $metaInformation;
	}

	/**
	 * Returns the version
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @todo missing docblock
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Returns the previewImage
	 *
	 * @return string
	 * @api
	 */
	public function getManualUrl() {
		return '';
	}

	/**
	 * @return string
	 */
	public function getTypoScriptConfig() {
		if (file_exists($this->getTypoScriptConfigAbsPath()) && is_file($this->getTypoScriptConfigAbsPath())) {
			return file_get_contents($this->getTypoScriptConfigAbsPath());
		}
		return '';
	}

	/**
	 * @return string
	 */
	public function getTypoScriptConfigAbsPath() {
		return $this->pathTsConfig;
	}

	/**
	 * @return string
	 */
	public function getTypoScriptAbsPath() {
		return $this->pathTyposcript;
	}

	/**
	 * @return string
	 */
	public function getTypoScriptConstantsAbsPath() {
		return $this->pathTyposcriptConstants;
	}

	/**
	 * returns the relative path of the theme
	 *
	 * @return string
	 */
	public function getRelativePath() {
		if (ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
			return ExtensionManagementUtility::siteRelPath($this->getExtensionName());
		}
		return '';
	}

	/**
	 * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
	 *
	 * @param array Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
	 * @param \TYPO3\CMS\Core\TypoScript\TemplateService Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return void
	 */
	public function addTypoScriptForFe(&$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj) {
		// @codingStandardsIgnoreStart
		$themeItem = array(
			'constants' => @is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
			'config' => @is_file($this->getTypoScriptAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptAbsPath()) : '',
			'include_static' => '',
			'include_static_file' => '',
			'title' => 'themes:' . $this->getExtensionName(),
			'uid' => md5($this->getExtensionName())
		);
		// @codingStandardsIgnoreEnd

		$themeItem['constants'] .= $this->getBasicConstants($params['pid']);
		$themeItem['constants'] .= LF . $this->getTypoScriptForLanguage($params, $pObj);

		$pObj->processTemplate(
			$themeItem,
			$params['idList'] . ',ext_themes' . str_replace('_', '', $this->getExtensionName()),
			$params['pid'],
			'ext_themes' . str_replace('_', '', $this->getExtensionName()),
			$params['templateId']
		);
	}

	/**
	 * @param $params
	 * @param \TYPO3\CMS\Core\TypoScript\TemplateService $pObj
	 * @return string
	 */
	public function getTypoScriptForLanguage(&$params, &$pObj) {
		if (!is_object($GLOBALS['TYPO3_DB'])) {
			return '';
		}

		$languages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'sys.uid as uid, sys.title as title, sys.flag as flag,static.lg_name_local as lg_name_local,static.lg_name_en as lg_name_en, static.lg_collate_locale as lg_collate_locale',
			'sys_language sys,static_languages static', 'sys.static_lang_isocode = static.uid AND sys.hidden=0'
		);

		$outputBuffer = '';
		$languageUids = array();
		$key = 'themes.languages';

		if (is_array($languages)) {
			foreach ($languages as $language) {
				$languageUids[] = $language['uid'];
				$buffer = '[globalVar = GP:L=' . $language['uid'] . ']' . LF;
				$buffer .= $key . '.current {' . LF;
				$buffer .= ' uid = ' . $language['uid'] . LF;
				$buffer .= ' label = ' . $language['title'] . LF;
				$buffer .= ' labelLocalized = ' . $language['lg_name_local'] . LF;
				$buffer .= ' labelEnglish = ' . $language['lg_name_en'] . LF;
				$buffer .= ' flag = ' . $language['flag'] . LF;
				$buffer .= ' isoCode = ' . $language['lg_collate_locale'] . LF;
				$buffer .= ' isoCodeShort = ' . array_shift(explode('_', $language['lg_collate_locale'])) . LF;
				$buffer .= ' isoCodeHtml = ' . str_replace('_', '-', $language['lg_collate_locale']) . LF;
				$buffer .= '} ' . LF;
				$buffer .= '[global]' . LF;
				$outputBuffer .= $buffer;
			}
			$outputBuffer .= $key . '.available=' . implode(',', $languageUids) . LF;
		} else {
			$outputBuffer .= $key . '.available=' . LF;
		}

		/** @var \TYPO3\CMS\Lang\Domain\Model\Language $language */
		return $outputBuffer;
	}

	protected function getBasicConstants($pid) {
		$buffer = '';
		$buffer .= LF . 'themes.relativePath = ' . $this->getRelativePath();
		$buffer .= LF . 'themes.name = ' . $this->getExtensionName();
		$buffer .= LF . 'themes.templatePageId = ' . $pid;
		$buffer .= LF . 'themes.mode.context = ' . ApplicationContext::getApplicationContext();
		$buffer .= LF . 'themes.mode.isDevelopment = ' . (int)ApplicationContext::isDevelopmentModeActive();
		$buffer .= LF . 'themes.mode.isProduction = ' . (int)!ApplicationContext::isDevelopmentModeActive();
		return $buffer;
	}

}
