<?php

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

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class ThemeController.
 */
class ThemeController extends ActionController
{
    /**
     * @var string
     */
    protected $templateName = '';

    /**
     * @var array
     */
    protected $typoScriptSetup;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
     */
    protected $themeRepository;

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT;
        $this->typoScriptSetup = $this->configurationManager->getConfiguration($configurationType);
    }

    /**
     * @param \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository
     */
    public function injectThemeRepository(ThemeRepository $themeRepository)
    {
        $this->themeRepository = $themeRepository;
    }

    /**
     * @param \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository
     */
    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * renders the given theme.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->templateName = $this->evaluateTypoScript('plugin.tx_themes.view.templateName');
        $templateFile = $this->getTemplateFile();
        if ($templateFile !== null) {
            $this->view->setTemplatePathAndFilename($templateFile);
        }
        $this->view->assign('templateName', $this->templateName);

        $frontendController = $this->getFrontendController();
        $this->view->assign('theme', $this->themeRepository->findByPageOrRootline($frontendController->id));
        // Get page data
        $pageArray = $this->pageRepository->getPage($frontendController->id);
        $pageArray['icon'] = '';
        // Map page icon
        if (isset($pageArray['tx_themes_icon']) && $pageArray['tx_themes_icon'] != '') {
            $setup = $frontendController->tmpl->setup;
            $pageArray['icon'] = $setup['lib.']['icons.']['cssMap.'][$pageArray['tx_themes_icon']];
        }
        // Get settings, because they aren't available
        $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK;
        $configuration = $this->configurationManager->getConfiguration($configurationType, 'Themes');
        if(isset($configuration['settings']['templateName']))
            unset($configuration['settings']['templateName']);
        $this->view->assign('settings', $configuration['settings']);
        $this->view->assign('page', $pageArray);
        $this->view->assign('data', $pageArray);
        $this->view->assign('TSFE', $frontendController);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * renders a given TypoScript Path.
     *
     * @param $path
     *
     * @return string
     */
    protected function evaluateTypoScript($path)
    {
        /** @var $vh \TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper */
        $vh = $this->objectManager->get(\TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper::class);
        $vh->setRenderChildrenClosure(function () {
            return '';
        });

        $vh->setArguments(['typoscriptObjectPath' => $path]);
        /** @var \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext $renderingContext */
        $renderingContext = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\Core\Rendering\RenderingContext::class);
        $vh->setRenderingContext($renderingContext);
        return $vh->render();
    }

    /**
     * gets a TS Array by path.
     *
     * @param $typoscriptObjectPath
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     *
     * @return array
     */
    protected function getTsArrayByPath($typoscriptObjectPath)
    {
        $pathSegments = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $typoscriptObjectPath);
        $setup = $this->typoScriptSetup;
        foreach ($pathSegments as $segment) {
            if (!array_key_exists(($segment . '.'), $setup)) {
                throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('TypoScript object path "' . htmlspecialchars($typoscriptObjectPath) . '" does not exist', 1253191023);
            }
            $setup = $setup[$segment . '.'];
        }

        return $setup;
    }

    /**
     * get the needed templateFile from TS.
     *
     * @return null|string
     */
    protected function getTemplateFile()
    {
        $templatePaths = $this->getTsArrayByPath('plugin.tx_themes.view.templateRootPaths');
        krsort($templatePaths);
        foreach ($templatePaths as $templatePath) {
            $cleanedPath = GeneralUtility::getFileAbsFileName($templatePath) . 'Theme/' . $this->templateName . '.html';
            if (is_file($cleanedPath)) {
                return $cleanedPath;
            }
        }
        return null;
    }
}
