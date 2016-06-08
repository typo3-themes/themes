<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\CheckPageUtility;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

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
		// Get extension configuration
		/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
		$configurationUtility = $this->objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
		$extensionConfiguration = $configurationUtility->getCurrentConfiguration('themes');
		// Initially, get configuration from extension manager!
		$extensionConfiguration['categoriesToShow'] = GeneralUtility::trimExplode(',', $extensionConfiguration['categoriesToShow']['value']);
		$extensionConfiguration['constantsToHide'] = GeneralUtility::trimExplode(',', $extensionConfiguration['constantsToHide']['value']);
		// mod.tx_themes.constantCategoriesToShow.value
		// Get value from page/user typoscript
		$externalConstantCategoriesToShow = $this->getBackendUser()->getTSConfig(
			'mod.tx_themes.constantCategoriesToShow', BackendUtility::getPagesTSconfig($this->id)
		);
		if ($externalConstantCategoriesToShow['value']) {
			$this->externalConfig['constantCategoriesToShow'] = GeneralUtility::trimExplode(',', $externalConstantCategoriesToShow['value']);
			ArrayUtility::mergeRecursiveWithOverrule(
				$extensionConfiguration['categoriesToShow'],
				$this->externalConfig['constantCategoriesToShow']
			);
		}
		// mod.tx_themes.constantsToHide.value
		// Get value from page/user typoscript
		$externalConstantsToHide = $this->getBackendUser()->getTSConfig(
			'mod.tx_themes.constantsToHide', BackendUtility::getPagesTSconfig($this->id)
		);
		if ($externalConstantsToHide['value']) {
			$this->externalConfig['constantsToHide'] = GeneralUtility::trimExplode(',', $externalConstantsToHide['value']);
			ArrayUtility::mergeRecursiveWithOverrule(
				$extensionConfiguration['constantsToHide'], 
				$this->externalConfig['constantsToHide']
			);
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
			$categoriesFilterSettings = $this->getBackendUser()->getModuleData('mod-web_ThemesMod1/Categories/Filter/Settings', 'ses');
			if($categoriesFilterSettings === NULL) {
				$categoriesFilterSettings = array();
				$categoriesFilterSettings['searchScope'] = 'all';
				$categoriesFilterSettings['showBasic'] = '1';
				$categoriesFilterSettings['showAdvanced'] = '0';
				$categoriesFilterSettings['showExpert'] = '0';
			}
			$this->view->assign('categoriesFilterSettings', $categoriesFilterSettings);
		} elseif ($this->id !== 0) {
			$nearestPageWithTheme = FindParentPageWithThemeUtility::find($this->id);
		} else {
			$nearestPageWithTheme = 0;
		}

		$this->view->assignMultiple(
			array(
				'pid'                  => $this->id,
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

		$this->themeRepository->findByUid(array())->getAllPreviewImages();
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
				'pid' => $this->id,
			)
		);
	}

	/**
	 * activate a theme
	 *
	 * @param string $theme
	 * @return void
	 */
	public function showThemeDetailsAction($theme = NULL) {
		$themeObject = $this->themeRepository->findByIdentifier($theme);
		$this->view->assign('theme', $themeObject);
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
			$user = clone $this->getBackendUser();
			$user->user['admin'] = 1;
			$tce->start($record, Array(), $user);
			$tce->process_datamap();
			$tce->clear_cacheCmd('pages');
		} else {
			$this->flashMessageContainer->add('Problem selecting theme');
		}
		$this->redirect('index');
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
					'key' => $categoryName,
					'title' => $title,
					'items' => array(),
				);
				foreach (array_keys($category) as $constantName) {
					if (($deniedFields === NULL) || (!in_array($constantName, $deniedFields))) {
						if (isset($subcategories[$constants[$constantName]['subcat_name']][0])) {
							$constants[$constantName]['subcat_name'] = $subcategories[$constants[$constantName]['subcat_name']][0];
						}
						// Basic, advanced or expert?!
						$constants[$constantName]['userScope'] = 'advanced';
						if(isset($categories['basic']) && array_key_exists($constants[$constantName]['name'], $categories['basic'])) {
							$constants[$constantName]['userScope'] = 'basic';
						}
						else if(isset($categories['advanced']) && array_key_exists($constants[$constantName]['name'], $categories['advanced'])) {
							$constants[$constantName]['userScope'] = 'advanced';
						}
						else if(isset($categories['expert']) && array_key_exists($constants[$constantName]['name'], $categories['expert'])) {
							$constants[$constantName]['userScope'] = 'expert';
						}
						// Only get the first category
						$catParts = explode(',', $constants[$constantName]['cat']);
						if(isset($catParts[1])) {
							$constants[$constantName]['cat'] = $catParts[0];
						}
						// Extract sub category
						$subcatParts = explode('/', $constants[$constantName]['subcat']);
						if(isset($subcatParts[1])) {
							$constants[$constantName]['subCategory'] = $subcatParts[1];
						}
						$definition[$categoryName]['items'][] = $constants[$constantName];
					}
				}
			}
		}
		return array_values($definition);
	}

	public function saveCategoriesFilterSettingsAction() {
		// Validation definition
		$validSettings = array(
			'searchScope' => 'string', 
			'showBasic' => 'boolean', 
			'showAdvanced' => 'boolean', 
			'showExpert' => 'boolean'
		);
		// Validate params
		$categoriesFilterSettings = array();
		foreach($validSettings as $setting => $type) {
			if($this->request->hasArgument($setting)) {
				if($type == 'boolean') {
					$categoriesFilterSettings[$setting] = (bool)$this->request->getArgument($setting) ? '1' : '0';
				}
				else if($type == 'string') {
					$categoriesFilterSettings[$setting] = ctype_alpha($this->request->getArgument($setting)) ? $this->request->getArgument($setting) : 'all';
				}
			}
		}
		// Save settings
		$this->getBackendUser()->pushModuleData('mod-web_ThemesMod1/Categories/Filter/Settings', $categoriesFilterSettings);
		// Create JSON-String
		$response = array();
		$response['success'] = '';
		$response['error'] = '';
		$response['data'] = $categoriesFilterSettings;
		$json = json_encode($response);
		return $json;
	}

	/**
	 * @return BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}
