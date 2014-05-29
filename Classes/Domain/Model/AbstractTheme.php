<?php

namespace KayStrobach\Themes\Domain\Model;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo get rid of getExtensionname, use EXT:extname as theme name to avoid conflicts in the database
 *
 * Class AbstractTheme
 * @package KayStrobach\Themes\Domain\Model
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
	protected $pathTSConfig;

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
	 * Returns the previewImage
	 *
	 * @return string
	 * @api
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

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
	public function getTSConfig() {
		if(file_exists($this->getTSConfigAbsPath()) && is_file($this->getTSConfigAbsPath())) {
			return file_get_contents($this->getTSConfigAbsPath());
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 */
	public function getTSConfigAbsPath() {
		return $this->pathTSConfig;
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
	 * @param	\TYPO3\CMS\Core\TypoScript\TemplateService		Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return	void
	 */
	public function addTypoScriptForFe(&$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj) {
		$themeItem = array(
			'constants'=>	@is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
			'config'=>		@is_file($this->getTypoScriptAbsPath())          ? GeneralUtility::getUrl($this->getTypoScriptAbsPath()) : '',
			'include_static'=>	'',
			'include_static_file'=>	'',
			'title' =>	'themes:' . $this->getExtensionName(),
			'uid' => md5($this->getExtensionName())
		);

		$themeItem['constants'] .= chr(10) . 'themes.relPath     = ' . $this->getRelativePath();
		$themeItem['constants'] .= chr(10) . 'themes.name        = ' . $this->getExtensionName();
		$themeItem['constants'] .= chr(10) . 'themes.templatePid = ' . $params['pid'];
		$themeItem['constants'] .= chr(10) . $this->getTypoScriptForLanguage($params, $pObj);

			$pObj->processTemplate(
			$themeItem,
			$params['idList'] . ',ext_themes' . str_replace('_', '', $this->getExtensionName()),
			$params['pid'],
			'ext_themes' . str_replace('_', '', $this->getExtensionName()),
			$params['templateId']
		);
	}

	public function getTypoScriptForLanguage(&$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj) {
		/**
		 * @var \TYPO3\CMS\Core\Database\DatabaseConnection                 $TYPO3_DB
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager                     $objectManager
		 */
		global $TYPO3_DB;

		$languages = $TYPO3_DB->exec_SELECTgetRows(
			'sys.uid as uid, sys.title as title, sys.flag as flag,static.lg_name_local as lg_name_local, static.lg_name_en as lg_name_en, static.lg_collate_locale as lg_collate_locale',
			'sys_language sys,static_languages static',
			'sys.static_lang_isocode = static.uid AND sys.hidden=0'
		);

		$outputBuffer = '';
		$languageUids = array();
		$key          = 'themes.languages';

		foreach($languages as $language) {
			$languageUids[] = $language['uid'];

			$buffer = '[globalVar = GP:L=' . $language['uid'] . ']' . chr(10);
			$buffer .= $key . '.current {' . chr(10);
			$buffer .= '  label = '          . $language['title'] . chr(10);
			$buffer .= '  labelLocalized = ' . $language['lg_name_local'] . chr(10);
			$buffer .= '  label = '          . $language['lg_name_en'] . chr(10);
			$buffer .= '  flag = '           . $language['flag'] . chr(10);
			$buffer .= '  isoCode = '        . $language['lg_collate_locale'] . chr(10);
			$buffer .= '  isoCodeHtml = '    . str_replace('_', '-', $language['lg_collate_locale']) . chr(10);
			$buffer .= '}  ' . chr(10);
			$buffer .= '[global]' . chr(10);

			$outputBuffer.= $buffer;

		}

		$outputBuffer .= $key . '.available=' . implode(',', $languageUids) . chr(10);

		/** @var \TYPO3\CMS\Lang\Domain\Model\Language $language */

		return $outputBuffer;
	}
}
