<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Controller;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use Doctrine\DBAL\DBALException;
use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use KayStrobach\Themes\Utilities\CheckPageUtility;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\TsParserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
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
    protected string $extensionName = 'Themes';

    /**
     * @var int
     */
    protected int $id = 0;

    /**
     * @var ThemeRepository
     */
    protected ThemeRepository $themeRepository;

    /**
     * @var TsParserUtility
     */
    protected TsParserUtility $tsParser;

    /**
     * @var array
     * external config.
     */
    protected array $externalConfig = [];

    /**
     * @var array
     */
    protected array $deniedFields = [];

    /**
     * @var array
     */
    protected array $allowedCategories = [];

    /**
     * @var IconFactory
     */
    protected IconFactory $iconFactory;

    /**
     * @var Theme
     */
    protected Theme $selectedTheme;

    private ModuleTemplateFactory $moduleTemplateFactory;

    private PageRenderer $pageRenderer;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory, PageRenderer $pageRenderer)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param ThemeRepository $themeRepository
     */
    public function injectThemeRepository(ThemeRepository $themeRepository)
    {
        $this->themeRepository = $themeRepository;
    }

    /**
     * show available constants.
     *
     * @return ResponseInterface
     * @throws DBALException
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->view->assign('selectableThemes', $this->themeRepository->findAll());
        if (!empty($this->selectedTheme)) {
            $nearestPageWithTheme = $this->id;
            $this->view->assign('selectedTheme', $this->selectedTheme);
            $this->view->assign(
                'categories',
                $this->renderFields(
                    $this->tsParser,
                    $this->id,
                    $this->allowedCategories,
                    $this->deniedFields
                )
            );
            $categoriesFilterSettings = $this->getBackendUser()->getModuleData(
                'mod-web_ThemesMod1/Categories/Filter/Settings',
                'ses'
            );
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

        $this->view->assign('pid', $this->id);
        $this->view->assign('nearestPageWithTheme', $nearestPageWithTheme);
        $this->view->assign('themeIsSelectable', CheckPageUtility::hasThemeableSysTemplateRecord($this->id));
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    /**
     * @param TsParserUtility $tsParserWrapper
     * @param $pid
     * @param array|null $allowedCategories
     * @param array|null $deniedFields
     *
     * @return array
     */
    protected function renderFields(
        TsParserUtility $tsParserWrapper,
        $pid,
        ?array $allowedCategories = null,
        ?array $deniedFields = null
    ): array {
        $definition = [];
        $categories = $tsParserWrapper->getCategories($pid);
        $constants = $tsParserWrapper->getConstants($pid);
        foreach ($categories as $categoryName => $category) {
            asort($category);
            if (is_array($category) && (($allowedCategories === null) || (in_array($categoryName, $allowedCategories)))) {
                $title = $GLOBALS['LANG']->sL(
                    'LLL:EXT:themes/Resources/Private/Language/Constants/locallang.xml:cat_' . $categoryName
                );
                if (strlen($title) === 0) {
                    $title = $categoryName;
                }
                $definition[$categoryName] = [
                        'key' => $categoryName,
                        'title' => $title,
                        'items' => [],
                ];
                foreach (array_keys($category) as $constantName) {
                    if (($deniedFields === null) || (!in_array($constantName, $deniedFields))) {
                        // Basic, advanced or expert?!
                        $constants[$constantName]['userScope'] = 'advanced';
                        if (isset($categories['basic']) && array_key_exists(
                            $constants[$constantName]['name'],
                            $categories['basic']
                        )) {
                            $constants[$constantName]['userScope'] = 'basic';
                        } elseif (isset($categories['advanced']) && array_key_exists(
                            $constants[$constantName]['name'],
                            $categories['advanced']
                        )) {
                            $constants[$constantName]['userScope'] = 'advanced';
                        } elseif (isset($categories['expert']) && array_key_exists(
                            $constants[$constantName]['name'],
                            $categories['expert']
                        )) {
                            $constants[$constantName]['userScope'] = 'expert';
                        }
                        // Only get the first category
                        $catParts = explode(',', $constants[$constantName]['cat']);
                        if (isset($catParts[1])) {
                            $constants[$constantName]['cat'] = $catParts[0];
                        }
                        $definition[$categoryName]['items'][] = $constants[$constantName];
                    }
                }
            }
        }

        return array_values($definition);
    }

    /**
     * save changed constants.
     *
     * @param array $data
     * @param array $check
     * @param int $pid
     *
     * @throws StopActionException
     */
    public function updateAction(array $data, array $check, int $pid)
    {
        /*
         * @todo check wether user has access to page BEFORE SAVE!
         */
        $this->tsParser->applyToPid($pid, $data, $check);
        $this->redirect('index');

        $this->themeRepository->findByUid(0)->getAllPreviewImages();
    }

    /**
     * Show theme details.
     *
     * @return ResponseInterface
     * @throws DBALException
     */
    public function showThemeAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->view->assignMultiple(
            [
                    'selectedTheme' => $this->selectedTheme,
                    'selectableThemes' => $this->themeRepository->findAll(),
                    'themeIsSelectable' => CheckPageUtility::hasThemeableSysTemplateRecord($this->id),
                    'pid' => $this->id,
            ]
        );
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    /**
     * activate a theme.
     *
     * @param null $theme
     *
     * @return ResponseInterface
     */
    public function showThemeDetailsAction($theme = null): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $themeObject = $this->themeRepository->findByIdentifier($theme);
        $this->view->assign('theme', $themeObject);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    /**
     * activate a theme.
     *
     * @param string $theme
     *
     * @throws StopActionException
     * @throws DBALException
     */
    public function setThemeAction(string $theme = '')
    {
        $sysTemplateRecordUid = CheckPageUtility::getThemeableSysTemplateRecord($this->id);
        if (($sysTemplateRecordUid !== false) && ($theme !== '')) {
            $record = [
                    'sys_template' => [
                            $sysTemplateRecordUid => [
                                    'tx_themes_skin' => $theme,
                            ],
                    ],
            ];
            $tce = new DataHandler();
            $user = clone $this->getBackendUser();
            $user->user['admin'] = 1;
            $tce->start($record, [], $user);
            $tce->process_datamap();
            $tce->clear_cacheCmd('pages');
        } else {
            $this->addFlashMessage('Problem selecting theme', '', AbstractMessage::ERROR);
        }
        $this->redirect('index');
    }

    /**
     * @throws NoSuchArgumentException
     */
    public function saveCategoriesFilterSettingsAction(): ResponseInterface
    {
        // Validation definition
        $validSettings = [
                'searchScope' => 'string',
                'showBasic' => 'boolean',
                'showAdvanced' => 'boolean',
                'showExpert' => 'boolean',
        ];
        // Validate params
        $categoriesFilterSettings = [];
        foreach ($validSettings as $setting => $type) {
            if ($this->request->hasArgument($setting)) {
                if ($type == 'boolean') {
                    $categoriesFilterSettings[$setting] = $this->request->getArgument($setting) ? '1':'0';
                } elseif ($type == 'string') {
                    try {
                        $categoriesFilterSettings[$setting] = ctype_alpha(
                            $this->request->getArgument($setting)
                        ) ? $this->request->getArgument($setting):'all';
                    } catch (NoSuchArgumentException $e) {
                    }
                }
            }
        }
        // Save settings
        $this->getBackendUser()->pushModuleData(
            'mod-web_ThemesMod1/Categories/Filter/Settings',
            $categoriesFilterSettings
        );
        //
        // Create JSON-String
        $response = [
                'success' => '',
                'error' => '',
                'data' => $categoriesFilterSettings,
        ];
        return $this->jsonResponse(json_encode($response));
    }

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @throws DBALException
     */
    protected function initializeView(ViewInterface $view)
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        if (!empty($moduleTemplate)) {
            $pageRenderer = $this->pageRenderer;
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/Colorpicker');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/ThemesBackendModule');
            $extRealPath = '../' . PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('themes'));
            $pageRenderer->addCssFile($extRealPath . 'Resources/Public/Stylesheet/BackendModule.css');
            $pageRenderer->addCssFile($extRealPath . 'Resources/Public/Contrib/colorpicker/css/colorpicker.css');
            // Initialize icon factory
            $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            // Try to load the selected theme
            $this->selectedTheme = $this->themeRepository->findByPageId($this->id);
            // Create menu and buttons
            $this->createMenu();
            $this->createButtons();
        }
    }

    /**
     * Create action menu
     */
    protected function createMenu(): void
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('themes');
        $actions = [
                [
                        'action' => 'index',
                        'label' => LocalizationUtility::translate(
                            'setConstants',
                            $this->request->getControllerExtensionName()
                        ),
                ],
                [
                        'action' => 'showTheme',
                        'label' => LocalizationUtility::translate(
                            'setTheme',
                            $this->request->getControllerExtensionName()
                        ),
                ],
        ];
        foreach ($actions as $action) {
            $item = $menu->makeMenuItem()
                    ->setTitle($action['label'])
                    ->setHref($uriBuilder->reset()->uriFor($action['action'], [], 'Editor'))
                    ->setActive($this->request->getControllerActionName() === $action['action']);
            $menu->addMenuItem($item);
        }
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Add menu buttons for specific actions
     */
    protected function createButtons()
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        $buttons = [];
        switch ($this->request->getControllerActionName()) {
            case 'index':
                {
                    // Only show save button, in case of a theme is selected
                    if (!empty($this->selectedTheme)) {
                        $buttons[] = $buttonBar->makeInputButton()
                                ->setName('save')
                                ->setValue('1')
                                ->setForm('saveableForm')
                                ->setIcon($this->iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL))
                                ->setTitle('Save');
                    }
                    $buttons[] = $buttonBar->makeLinkButton()
                            ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor('showTheme', [], 'Editor'))
                            ->setTitle('Choose theme')
                            ->setIcon($this->iconFactory->getIcon('actions-system-options-view', Icon::SIZE_SMALL));
                    break;
                }
            case 'showTheme':
            case 'showThemeDetails':
                {
                    $buttons[] = $buttonBar->makeLinkButton()
                            ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor('index', [], 'Editor'))
                            ->setTitle('Go back')
                            ->setIcon($this->iconFactory->getIcon('actions-view-go-back', Icon::SIZE_SMALL));
                    break;
                }
        }
        foreach ($buttons as $button) {
            $buttonBar->addButton($button);
        }
    }

    /**
     * Initializes the controller before invoking an action method.
     */
    protected function initializeAction()
    {
        $this->id = (int)GeneralUtility::_GET('id');
        $this->tsParser = new TsParserUtility();
        // Get extension configuration
        try {
            $extensionConfiguration = $this->getExtensionConfiguration('themes');
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException $e) {
        }
        $extensionConfiguration = $extensionConfiguration ?? [];
        // Initially, get configuration from extension manager!
        $extensionConfiguration['categoriesToShow'] = GeneralUtility::trimExplode(
            ',',
            $extensionConfiguration['categoriesToShow']
        );
        $extensionConfiguration['constantsToHide'] = GeneralUtility::trimExplode(
            ',',
            $extensionConfiguration['constantsToHide']
        );
        // mod.tx_themes.constantCategoriesToShow.value
        // Get value from page/user typoscript
        $externalConstantCategoriesToShow = $this->getBackendUser()->getTSConfig(
        )['mod.']['tx_themes.']['constantCategoriesToShow.'] ?? null;
        if ($externalConstantCategoriesToShow['value']) {
            $this->externalConfig['constantCategoriesToShow'] = GeneralUtility::trimExplode(
                ',',
                $externalConstantCategoriesToShow['value']
            );
            $extensionConfiguration['categoriesToShow'] = array_merge(
                $extensionConfiguration['categoriesToShow'],
                $this->externalConfig['constantCategoriesToShow']
            );
        }
        // mod.tx_themes.constantsToHide.value
        // Get value from page/user typoscript
        $externalConstantsToHide = $this->getBackendUser()->getTSConfig(
        )['mod.']['tx_themes.']['constantsToHide.'] ?? null;
        if ($externalConstantsToHide['value']) {
            $this->externalConfig['constantsToHide'] = GeneralUtility::trimExplode(
                ',',
                $externalConstantsToHide['value']
            );
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
     * @param string $extensionKey
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getExtensionConfiguration(string $extensionKey): array
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        /** @var array $configuration */
        $configuration = $extensionConfiguration->get($extensionKey);
        return $configuration;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
