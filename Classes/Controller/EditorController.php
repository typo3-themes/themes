<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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

        // extension configuration
        $t = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['themes']);
        $t['categoriesToShow'] = GeneralUtility::trimExplode(',', $t['categoriesToShow']);
        $t['constantsToHide'] = GeneralUtility::trimExplode(',', $t['constantsToHide']);

        // mod.tx_themes.constantCategoriesToShow.value
		$externalConstantCategoriesToShow = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantCategoriesToShow',
			BackendUtility::getPagesTSconfig($this->id)
		);
        if($externalConstantCategoriesToShow['value']){
            $this->externalConfig['constantCategoriesToShow'] = GeneralUtility::trimExplode(',',$externalConstantCategoriesToShow['value']);
            ArrayUtility::mergeRecursiveWithOverrule($t['categoriesToShow'], $this->externalConfig['constantCategoriesToShow']);
        }else{
            $this->externalConfig['constantCategoriesToShow'] = array();
        }

        // mod.tx_themes.constantsToHide.value
		$externalConstantsToHide = $GLOBALS["BE_USER"]->getTSConfig(
			'mod.tx_themes.constantsToHide',
			BackendUtility::getPagesTSconfig($this->id)
		);
        if($externalConstantsToHide['value']){
            $this->externalConfig['constantsToHide'] = GeneralUtility::trimExplode(',',$externalConstantsToHide['value']);
            ArrayUtility::mergeRecursiveWithOverrule($t['constantsToHide'], $this->externalConfig['constantsToHide']);
        }else{
            $this->externalConfig['constantsToHide'] = array();
        }

		$this->allowedCategories = $t['categoriesToShow'];
		$this->deniedFields      = $t['constantsToHide'];

		// initialize normally used values
	}

	/**
	 * show available constants
	 */
	public function indexAction() {
		$this->view->assign('selectableThemes', $this->themeRepository->findAll());
		$selectedTheme        = $this->themeRepository->findByPageId($this->id);
		if ($selectedTheme !== NULL) {
			$nearestPageWithTheme = $this->id;
			$this->view->assign('selectedTheme', $selectedTheme);
			$this->view->assign('categories',    $this->renderFields($this->tsParser, $this->id, $this->allowedCategories, $this->deniedFields));
		} elseif($this->id !== 0) {
			$nearestPageWithTheme = FindParentPageWithThemeUtility::find($this->id);
		} else {
			$nearestPageWithTheme = 0;
		}

		$this->view->assignMultiple(
			array(
				'pid'                  => $this->id,
				'nearestPageWithTheme' => $nearestPageWithTheme,
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
        $subcategories = $tsParserWrapper->getSubCategories($pid);
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
                        if(isset($subcategories[$constants[$constantName]['subcat_name']][0])){
                            $constants[$constantName]['subcat_name'] = $subcategories[$constants[$constantName]['subcat_name']][0];
                        }
						$definition[$categorieName]['items'][] = $constants[$constantName];
					}
				}
			}
		}
		return array_values($definition);
	}
}