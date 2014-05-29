<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class EditorController extends ActionController {
	/**
	 * @var string Key of the extension this controller belongs to
	 */
	protected $extensionName = 'Themes';

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
	 * @inject
	 */
	protected $themeRepository;

	/**
	 * @var TsParserUtility
	 */
	protected $tsParser = NULL;

	/**
	 * external config
	 */
	protected $externalConfig = array();

	/**
	 * @var array
	 */
	protected $deniedFields = array();

	/**
	 * @var array
	 */
	protected $allowedCategories = array();

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->id = intval(GeneralUtility::_GET('id'));
		$this->tsParser = new TsParserUtility();

		$this->externalConfig['constantCategoriesToShow'] = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantCategoriesToShow',
			BackendUtility::getPagesTSconfig($this->id)
		);
		$this->externalConfig['constantsToHide'] = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantsToHide',
			BackendUtility::getPagesTSconfig($this->id)
		);

		// @todo add userTS / pageTS override
		$t = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['themes']);
		$this->allowedCategories = GeneralUtility::trimExplode(',', $t['categoriesToShow']);
		$this->deniedFields      = GeneralUtility::trimExplode(',', $t['constantsToHide']);
	}

	/**
	 * show available constants
	 */
	public function indexAction() {
		$this->view->assignMultiple(
			array(
				'selectedTheme'    => $this->themeRepository->findByPageId($this->id),
				'selectableThemes' => $this->themeRepository->findAll(),
				'categories'       => $this->renderFields($this->tsParser, $this->id, $this->allowedCategories, $this->deniedFields),
				'pid'              => $this->id
			)
		);
	}

	/**
	 * save changed constants
	 *
	 * @param array $data
	 * @param array $check
	 * @param integer $pid
	 */
	public function updateAction(array $data, array $check, $pid) {
		/**
		 * @todo check wether user has access to page BEFORE SAVE!
		 */
		$this->tsParser->applyToPid($pid, $data, $check);
		$this->redirect('index');
	}

	public function showThemeAction() {
		$this->view->assignMultiple(
			array(
				'selectedTheme'    => $this->themeRepository->findByPageId($this->id),
				'selectableThemes' => $this->themeRepository->findAll(),
				'pid'              => $this->id
			)
		);
	}

	/**
	 * @param \KayStrobach\Themes\Domain\Model\Theme $theme
	 * @param integer $pid
	 */
	public function setThemeAction(Theme $theme=NULL, $pid=0) {

	}


	/**
	 * @param \KayStrobach\Themes\Utilities\TsParserUtility $tsParserWrapper
	 * @param $pid
	 * @param null|array $allowedCategories
	 * @param null|array $deniedFields
	 * @return array
	 */
	protected function renderFields(TsParserUtility $tsParserWrapper, $pid, $allowedCategories = NULL, $deniedFields = NULL) {
		$definition = array();
		$categories = $tsParserWrapper->getCategories($pid);
		$constants  = $tsParserWrapper->getConstants($pid);
		foreach($categories as $categorieName => $categorie) {
			asort($categorie);
			if(is_array($categorie) && (($allowedCategories===NULL) || (in_array($categorieName, $allowedCategories)))) {
				$title = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/Constants/locallang.xml:cat_' . $categorieName);
				if(strlen($title) === 0) {
					$title = $categorieName;
				}
				$definition[$categorieName] = array(
					//'title'  => $GLOBALS['LANG']->sL($categorieName),
					'title'  => $title,
					'items'  => array(),
				);
				foreach($categorie as $constantName => $type) {
					if(($deniedFields === NULL) || (!in_array($constantName, $deniedFields))) {
						$definition[$categorieName]['items'][] = $constants[$constantName];
					}
				}
			}
		}
		return array_values($definition);
	}
}