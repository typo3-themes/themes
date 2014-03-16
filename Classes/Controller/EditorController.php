<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		#$this->pageRenderer->addInlineLanguageLabelFile('EXT:workspaces/Resources/Private/Language/locallang.xml');
		$this->id = intval(GeneralUtility::_GET('id'));
		$this->tsParser = new TsParserUtility();

		$this->externalConfig['constantCategoriesToShow'] = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantCategoriesToShow',
			t3lib_BEfunc::getPagesTSconfig($this->id)
		);
		$this->externalConfig['constantsToHide'] = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantsToHide',
			t3lib_BEfunc::getPagesTSconfig($this->id)
		);
	}

	public function indexAction() {
		$this->view->assignMultiple(
			array(
				'selectedTheme'    => $this->themeRepository->findByPageId($this->id),
				'selectableThemes' => $this->themeRepository->findAll(),
				'categories'       => $this->renderFields($this->tsParser, $this->id),
				'pid'              => $this->id
			)
		);
	}

	/**
	 * @param \KayStrobach\Themes\Domain\Model\Theme $theme
	 * @param integer $pid
	 */
	public function changeTheme(Theme $theme, $pid) {

	}

	/**
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

	/**
	 * @param \KayStrobach\Themes\Utilities\TsParserUtility $tsParserWrapper
	 * @param $pid
	 * @return array
	 */
	protected function renderFields(TsParserUtility $tsParserWrapper, $pid) {
		$definition = array();
		$categories = $tsParserWrapper->getCategories($pid);
		$constants  = $tsParserWrapper->getConstants($pid);
		foreach($categories as $categorieName => $categorie) {
			asort($categorie);
			//@todo add dynamic filter
			if(is_array($categorie)) {
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
					$definition[$categorieName]['items'][] = $constants[$constantName];
				}
			}
		}
		return array_values($definition);
	}
}