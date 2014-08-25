<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\CheckPageUtility;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class EditorController
 *
 * @package KayStrobach\Themes\Controller
 */
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
		$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['themes']);
		$extensionConfiguration['categoriesToShow'] = GeneralUtility::trimExplode(',', $extensionConfiguration['categoriesToShow']);
		$extensionConfiguration['constantsToHide'] = GeneralUtility::trimExplode(',', $extensionConfiguration['constantsToHide']);

		// mod.tx_themes.constantCategoriesToShow.value
		$externalConstantCategoriesToShow = $GLOBALS['BE_USER']->getTSConfig(
			'mod.tx_themes.constantCategoriesToShow', BackendUtility::getPagesTSconfig($this->id)
		);
		if ($externalConstantCategoriesToShow['value']) {
			$this->externalConfig['constantCategoriesToShow'] = GeneralUtility::trimExplode(',', $externalConstantCategoriesToShow['value']);
			ArrayUtility::mergeRecursiveWithOverrule($extensionConfiguration['categoriesToShow'], $this->externalConfig['constantCategoriesToShow']);
		} else {
			$this->externalConfig['constantCategoriesToShow'] = array();
		}

		// mod.tx_themes.constantsToHide.value
		$externalConstantsToHide = $GLOBALS['BE_USER']->getTSConfig(
			'mod.tx_themes.constantsToHide', BackendUtility::getPagesTSconfig($this->id)
		);
		if ($externalConstantsToHide['value']) {
			$this->externalConfig['constantsToHide'] = GeneralUtility::trimExplode(',', $externalConstantsToHide['value']);
			ArrayUtility::mergeRecursiveWithOverrule($extensionConfiguration['constantsToHide'], $this->externalConfig['constantsToHide']);
		} else {
			$this->externalConfig['constantsToHide'] = array();
		}

		$this->allowedCategories = $extensionConfiguration['categoriesToShow'];
		$this->deniedFields = $extensionConfiguration['constantsToHide'];

		// initialize normally used values
	}

	/**
	 * show available constants
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('selectableThemes', $this->themeRepository->findAll());
		$selectedTheme = $this->themeRepository->findByPageId($this->id);
		if ($selectedTheme !== NULL) {
			$nearestPageWithTheme = $this->id;
			$this->view->assign('selectedTheme', $selectedTheme);
			$this->view->assign('categories', $this->renderFields($this->tsParser, $this->id, $this->allowedCategories, $this->deniedFields));
		} elseif ($this->id !== 0) {
			$nearestPageWithTheme = FindParentPageWithThemeUtility::find($this->id);
		} else {
			$nearestPageWithTheme = 0;
		}

		$this->view->assignMultiple(
			array(
				'pid' => $this->id,
				'nearestPageWithTheme' => $nearestPageWithTheme,
				'themeIsSelectable' => CheckPageUtility::hasThemeableSysTemplateRecord($this->id),
			)
		);
	}

	/**
	 * save changed constants
	 *
	 * @param array $data
	 * @param array $check
	 * @param integer $pid
	 * @return void
	 */
	public function updateAction(array $data, array $check, $pid) {
		/**
		 * @todo check wether user has access to page BEFORE SAVE!
		 */
		$this->tsParser->applyToPid($pid, $data, $check);
		$this->redirect('index');
	}

	/**
	 * Show theme details
	 *
	 * @return void
	 */
	public function showThemeAction() {
		$this->view->assignMultiple(
			array(
				'selectedTheme' => $this->themeRepository->findByPageId($this->id),
				'selectableThemes' => $this->themeRepository->findAll(),
				'themeIsSelectable' => CheckPageUtility::hasThemeableSysTemplateRecord($this->id),
				'pid' => $this->id
			)
		);
	}

	/**
	 * activate a theme
	 *
	 * @param string $theme
	 * @return void
	 */
	public function setThemeAction($theme = NULL) {
		$sysTemplateRecordUid = CheckPageUtility::getThemeableSysTemplateRecord($this->id);
		if (($sysTemplateRecordUid !== FALSE) && ($theme !== NULL)) {
			$record = array(
				'sys_template' => array(
					$sysTemplateRecordUid => array(
						'tx_themes_skin' => $theme
					)
				)
			);
			$tce = new \TYPO3\CMS\Core\DataHandling\DataHandler();
			$tce->stripslashes_values = 0;
			$user = clone $GLOBALS['BE_USER'];
			$user->user['admin'] = 1;
			$tce->start($record, Array(), $user);
			$tce->process_datamap();
			$tce->clear_cacheCmd('pages');
		} else {
			$this->flashMessageContainer->add('Problem selecting theme');
		}
		$this->redirect('showTheme');
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
		$constants = $tsParserWrapper->getConstants($pid);
		foreach ($categories as $categoryName => $category) {
			asort($category);
			if (is_array($category) && (($allowedCategories === NULL) || (in_array($categoryName, $allowedCategories)))) {
				$title = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/Constants/locallang.xml:cat_' . $categoryName);
				if (strlen($title) === 0) {
					$title = $categoryName;
				}
				$definition[$categoryName] = array(
					'title' => $title,
					'items' => array(),
				);
				foreach (array_keys($category) as $constantName) {
					if (($deniedFields === NULL) || (!in_array($constantName, $deniedFields))) {
						if (isset($subcategories[$constants[$constantName]['subcat_name']][0])) {
							$constants[$constantName]['subcat_name'] = $subcategories[$constants[$constantName]['subcat_name']][0];
						}
						$definition[$categoryName]['items'][] = $constants[$constantName];
					}
				}
			}
		}
		return array_values($definition);
	}

}
