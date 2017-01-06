<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Utilities\CheckPageUtility;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\ThemeEnabledCondition;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class EditorController.
 */
class EditorController extends ActionController
{
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
    protected $tsParser = null;

    /**
     * external config.
     */
    protected $externalConfig = [];

    /**
     * @var array
     */
    protected $deniedFields = [];

    /**
     * @var array
     */
    protected $allowedCategories = [];

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * Backend Template Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);

        if ($this->view->getModuleTemplate() !== null) {
            $pageRenderer = $this->view->getModuleTemplate()->getPageRenderer();
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/Colorpicker');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/ThemesBackendModule');

            $extRealPath = '../' . ExtensionManagementUtility::siteRelPath('themes');

            $pageRenderer->addCssFile($extRealPath . 'Resources/Public/Stylesheet/BackendModule.css');
            $pageRenderer->addCssFile($extRealPath . 'Resources/Public/Contrib/colorpicker/css/colorpicker.css');

            $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $this->createMenu();
            $this->createButtons();
        }
    }

    /**
     * Add menu buttons for specific actions
     *
     * @return void
     */
    protected function createButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $buttons = [];

        switch ($this->request->getControllerActionName()) {
            case 'index': {
                $buttons[] = $buttonBar->makeInputButton()
                    ->setName('save')
                    ->setValue('1')
                    ->setForm('saveableForm')
                    ->setIcon($this->iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL))
                    ->setTitle('Save');

                $buttons[] = $buttonBar->makeLinkButton()
                    ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor('showTheme', [], 'Editor'))
                    ->setTitle('Show theme')
                    ->setIcon($this->iconFactory->getIcon('actions-system-options-view', Icon::SIZE_SMALL));
                break;
            }
            case 'showTheme':
            case 'showThemeDetails': {
                $buttons[] = $buttonBar->makeLinkButton()
                    ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor('index', [], 'Editor'))
                    ->setTitle('Go back')
                    ->setIcon($this->iconFactory->getIcon('actions-view-go-back', Icon::SIZE_SMALL));

                if ($this->request->hasArgument('theme') && !ThemeEnabledCondition::isThemeEnabled($this->request->getArgument('theme'))) {
                    $buttons[] = $buttonBar->makeLinkButton()
                        ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor('setTheme', ['theme' => $this->request->getArgument('theme')], 'Editor'))
                        ->setTitle('Save theme')
                        ->setIcon($this->iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL));
                }

                break;
            }
        }

        foreach ($buttons as $button) {
            $buttonBar->addButton($button, ButtonBar::BUTTON_POSITION_LEFT);
        }
    }

    /**
     * Create action menu
     *
     *@return void
     */
    protected function createMenu()
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('themes');

        $actions = [
            ['action' => 'index',       'label' => LocalizationUtility::translate('setConstants', $this->extensionName)],
            ['action' => 'showTheme',   'label' => LocalizationUtility::translate('setTheme', $this->extensionName)]
        ];

        foreach ($actions as $action) {
            $item = $menu->makeMenuItem()
                ->setTitle($action['label'])
                ->setHref($uriBuilder->reset()->uriFor($action['action'], [], 'Editor'))
                ->setActive($this->request->getControllerActionName() === $action['action']);
            $menu->addMenuItem($item);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction()
    {
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
            $extensionConfiguration['categoriesToShow'] = array_merge(
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
            $extensionConfiguration['constantsToHide'] = array_merge(
                $extensionConfiguration['constantsToHide'],
                $this->externalConfig['constantsToHide']
            );
        }
        $this->allowedCategories = $extensionConfiguration['categoriesToShow'];
        $this->deniedFields = $extensionConfiguration['constantsToHide'];
        // initialize normally used values
    }

    /**
     * show available constants.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('selectableThemes', $this->themeRepository->findAll());
        $selectedTheme = $this->themeRepository->findByPageId($this->id);
        if ($selectedTheme !== null) {
            $nearestPageWithTheme = $this->id;
            $this->view->assign('selectedTheme', $selectedTheme);
            $this->view->assign('categories', $this->renderFields($this->tsParser, $this->id, $this->allowedCategories, $this->deniedFields));
            $categoriesFilterSettings = $this->getBackendUser()->getModuleData('mod-web_ThemesMod1/Categories/Filter/Settings', 'ses');
            if ($categoriesFilterSettings === null) {
                $categoriesFilterSettings = [];
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
            [
                'pid'                  => $this->id,
                'nearestPageWithTheme' => $nearestPageWithTheme,
                'themeIsSelectable'    => CheckPageUtility::hasThemeableSysTemplateRecord($this->id),
            ]
        );
    }

    /**
     * save changed constants.
     *
     * @param array $data
     * @param array $check
     * @param int   $pid
     *
     * @return void
     */
    public function updateAction(array $data, array $check, $pid)
    {
        /*
         * @todo check wether user has access to page BEFORE SAVE!
         */
        $this->tsParser->applyToPid($pid, $data, $check);
        $this->redirect('index');

        $this->themeRepository->findByUid([])->getAllPreviewImages();
    }

    /**
     * Show theme details.
     *
     * @return void
     */
    public function showThemeAction()
    {
        $this->view->assignMultiple(
            [
                'selectedTheme'     => $this->themeRepository->findByPageId($this->id),
                'selectableThemes'  => $this->themeRepository->findAll(),
                'themeIsSelectable' => CheckPageUtility::hasThemeableSysTemplateRecord($this->id),
                'pid'               => $this->id,
            ]
        );
    }

    /**
     * activate a theme.
     *
     * @param string $theme
     *
     * @return void
     */
    public function showThemeDetailsAction($theme = null)
    {
        $themeObject = $this->themeRepository->findByIdentifier($theme);
        $this->view->assign('theme', $themeObject);
    }

    /**
     * activate a theme.
     *
     * @param string $theme
     *
     * @return void
     */
    public function setThemeAction($theme = null)
    {
        $sysTemplateRecordUid = CheckPageUtility::getThemeableSysTemplateRecord($this->id);
        if (($sysTemplateRecordUid !== false) && ($theme !== null)) {
            $record = [
                'sys_template' => [
                    $sysTemplateRecordUid => [
                        'tx_themes_skin' => $theme,
                    ],
                ],
            ];
            $tce = new \TYPO3\CMS\Core\DataHandling\DataHandler();
            $tce->stripslashes_values = 0;
            $user = clone $this->getBackendUser();
            $user->user['admin'] = 1;
            $tce->start($record, [], $user);
            $tce->process_datamap();
            $tce->clear_cacheCmd('pages');
        } else {
            $this->addFlashMessage('Problem selecting theme', '', FlashMessage::ERROR);
        }
        $this->redirect('index');
    }

    /**
     * @param \KayStrobach\Themes\Utilities\TsParserUtility $tsParserWrapper
     * @param $pid
     * @param null|array $allowedCategories
     * @param null|array $deniedFields
     *
     * @return array
     */
    protected function renderFields(TsParserUtility $tsParserWrapper, $pid, $allowedCategories = null, $deniedFields = null)
    {
        $definition = [];
        $categories = $tsParserWrapper->getCategories($pid);
        $subcategories = $tsParserWrapper->getSubCategories($pid);
        $constants = $tsParserWrapper->getConstants($pid);
        foreach ($categories as $categoryName => $category) {
            asort($category);
            if (is_array($category) && (($allowedCategories === null) || (in_array($categoryName, $allowedCategories)))) {
                $title = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/Constants/locallang.xml:cat_'.$categoryName);
                if (strlen($title) === 0) {
                    $title = $categoryName;
                }
                $definition[$categoryName] = [
                    'key'   => $categoryName,
                    'title' => $title,
                    'items' => [],
                ];
                foreach (array_keys($category) as $constantName) {
                    if (($deniedFields === null) || (!in_array($constantName, $deniedFields))) {
                        if (isset($subcategories[$constants[$constantName]['subcat_name']][0])) {
                            $constants[$constantName]['subcat_name'] = $subcategories[$constants[$constantName]['subcat_name']][0];
                        }
                        // Basic, advanced or expert?!
                        $constants[$constantName]['userScope'] = 'advanced';
                        if (isset($categories['basic']) && array_key_exists($constants[$constantName]['name'], $categories['basic'])) {
                            $constants[$constantName]['userScope'] = 'basic';
                        } elseif (isset($categories['advanced']) && array_key_exists($constants[$constantName]['name'], $categories['advanced'])) {
                            $constants[$constantName]['userScope'] = 'advanced';
                        } elseif (isset($categories['expert']) && array_key_exists($constants[$constantName]['name'], $categories['expert'])) {
                            $constants[$constantName]['userScope'] = 'expert';
                        }
                        // Only get the first category
                        $catParts = explode(',', $constants[$constantName]['cat']);
                        if (isset($catParts[1])) {
                            $constants[$constantName]['cat'] = $catParts[0];
                        }
                        // Extract sub category
                        $subcatParts = explode('/', $constants[$constantName]['subcat']);
                        if (isset($subcatParts[1])) {
                            $constants[$constantName]['subCategory'] = $subcatParts[1];
                        }
                        $definition[$categoryName]['items'][] = $constants[$constantName];
                    }
                }
            }
        }

        return array_values($definition);
    }

    public function saveCategoriesFilterSettingsAction()
    {
        // Validation definition
        $validSettings = [
            'searchScope'  => 'string',
            'showBasic'    => 'boolean',
            'showAdvanced' => 'boolean',
            'showExpert'   => 'boolean',
        ];
        // Validate params
        $categoriesFilterSettings = [];
        foreach ($validSettings as $setting => $type) {
            if ($this->request->hasArgument($setting)) {
                if ($type == 'boolean') {
                    $categoriesFilterSettings[$setting] = (bool) $this->request->getArgument($setting) ? '1' : '0';
                } elseif ($type == 'string') {
                    $categoriesFilterSettings[$setting] = ctype_alpha($this->request->getArgument($setting)) ? $this->request->getArgument($setting) : 'all';
                }
            }
        }
        // Save settings
        $this->getBackendUser()->pushModuleData('mod-web_ThemesMod1/Categories/Filter/Settings', $categoriesFilterSettings);
        // Create JSON-String
        $response = [];
        $response['success'] = '';
        $response['error'] = '';
        $response['data'] = $categoriesFilterSettings;
        $json = json_encode($response);

        return $json;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
