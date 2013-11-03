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
	 * @var TsParserUtility
	 */
	protected $tsParser = NULL;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		#$this->pageRenderer->addInlineLanguageLabelFile('EXT:workspaces/Resources/Private/Language/locallang.xml');
		$this->id = intval(GeneralUtility::_GET('id'));
		$this->tsParser = new TsParserUtility();
	}

	public function indexAction() {
		/**
		 * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $repository
		 */
		$repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

		$this->view->assignMultiple(
			array(
				'selectedTheme'    => '',
				'selectableThemes' => $repository->findAll(),
				'categories'       => $this->renderFields($this->tsParser, $this->id),
			)
		);
	}

	/**
	 * @param \KayStrobach\Themes\Domain\Model\Theme $theme
	 */
	public function changeTheme(Theme $theme) {

	}

	/**
	 * @param array $properties
	 */
	public function updateAction(array $properties) {

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
				$title = $GLOBALS['LANG']->sL('LLL:EXT:sitemgr_template/Resources/Private/Language/Constants/locallang.xml:cat_' . $categorieName);
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