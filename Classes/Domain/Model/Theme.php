<?php

class Tx_Themes_Domain_Model_Theme extends Tx_Extbase_DomainObject_AbstractEntity {
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
	 * @var $string
	 */
	protected $pathTyposcriptConstants;

	/**
	 * @var $string
	 */
	protected $pathTSConfig;

	/**
	 * Constructs a new Theme
	 *
	 * @api
	 */
	public function __construct($extensionName) {

		$this->extensionName = $extensionName;

		if(t3lib_extMgm::isLoaded($extensionName)) {
			/**
			 * set needed path variables
			 */
			$path                          = t3lib_extMgm::extPath($this->getExtensionName()) . 'Configuration/Theme/';
			$this->pathTyposcript          = $path . 'setup.ts';
			$this->pathTyposcriptConstants = $path . 'constants.ts';
			$this->pathTSConfig            = $path . 'tsconfig.ts';

			/**
			 * @var $EM_CONF array
			 * @var $_EXTKEY string
			 */
			$_EXTKEY                 = $extensionName;
			include(t3lib_extMgm::extPath($this->getExtensionName()) . 'ext_emconf.php');
			$this->title             = $EM_CONF[$this->getExtensionName()]['title'];
			$this->description       = $EM_CONF[$this->getExtensionName()]['description'];

			$this->version           = $EM_CONF[$this->getExtensionName()]['version'];

			$this->author['name']    = $EM_CONF[$this->getExtensionName()]['author'];
			$this->author['email']   = $EM_CONF[$this->getExtensionName()]['author_email'];
			$this->author['company'] = $EM_CONF[$this->getExtensionName()]['author_company'];

			if(t3lib_extMgm::extPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.png') {
				$this->previewImage      = t3lib_extMgm::extRelPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.png';
			} elseif(t3lib_extMgm::extPath('themes') . 'Resources/Public/Images/screenshot.gif') {
				$this->previewImage      = t3lib_extMgm::extRelPath($this->getExtensionName()) . 'Resources/Public/Images/screenshot.gif';
			} else {
				$this->previewImage      = t3lib_extMgm::extRelPath('themes')                  . 'Resources/Public/Images/screenshot.gif';
			}
		}
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

	}

	/**
	 * @return string
	 */
	public function getTSConfig() {
		if(file_exists($this->getTSConfigAbsPath())) {
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
	/**
	 * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
	 *
	 * @param	array		Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
	 * @param	object		Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return	void
	 */
	public function addTypoScriptForFe(&$params, &$pObj) {
		$themeItem = array(
			'constants'=>	@is_file($this->getTypoScriptConstantsAbsPath()) ? t3lib_div::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
			'config'=>		@is_file($this->getTypoScriptAbsPath())          ? t3lib_div::getUrl($this->getTypoScriptAbsPath()) : '',
			'editorcfg'=>	'',
			'include_static'=>	'',
			'include_static_file'=>	'',
			'title' =>	'themes:' . $this->getExtensionName(),
			'uid' => md5($this->getExtensionName())
		);

		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.relPath     = ' . t3lib_extMgm::siteRelPath($this->getExtensionName());
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.name        = ' . $this->getExtensionName();
		$themeItem['constants'] .= chr(10) . 'plugin.tx_themes.templatePid = ' . $params['pid'];

		$pObj->processTemplate(
			$themeItem,
			$params['idList'] . ',themes_' . $this->getExtensionName(),
			$params['pid'],
			'themes_' . $this->getExtensionName(),
			$params['templateId']
		);
	}
}