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
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Class ThemeController.
 */
class ThemeController extends ActionController
{
    /**
     * @var string
     */
    protected string $templateName = '';

    /**
     * @var array
     */
    protected array $typoScriptSetup;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ThemeRepository
     */
    protected ThemeRepository $themeRepository;

    /**
     * @var PageRepository
     */
    protected PageRepository $pageRepository;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT;
        $this->typoScriptSetup = $this->configurationManager->getConfiguration($configurationType);
    }

    /**
     * @param ThemeRepository $themeRepository
     */
    public function injectThemeRepository(ThemeRepository $themeRepository)
    {
        $this->themeRepository = $themeRepository;
    }

    /**
     * @param PageRepository $pageRepository
     */
    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * renders the given theme.
     *
     * @return ResponseInterface
     * @throws DBALException
     */
    public function indexAction(): ResponseInterface
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
        if (isset($configuration['settings']['templateName'])) {
            unset($configuration['settings']['templateName']);
        }
        $this->view->assign('settings', $configuration['settings']);
        $this->view->assign('conf', $configuration);
        $this->view->assign('page', $pageArray);
        $this->view->assign('data', $pageArray);
        $this->view->assign('TSFE', $frontendController);
        return $this->htmlResponse();
    }

    /**
     * renders a given TypoScript Path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function evaluateTypoScript(string $path): string
    {
        /** @var CObjectViewHelper $vh */
        $vh = $this->objectManager->get(CObjectViewHelper::class);
        $vh->setRenderChildrenClosure(function () {
            return '';
        });

        $vh->setArguments(['typoscriptObjectPath' => $path]);
        /** @var RenderingContext $renderingContext */
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $renderingContext->setRequest($this->request);
        $vh->setRenderingContext($renderingContext);
        return $vh->render();
    }

    /**
     * get the needed templateFile from TS.
     *
     * @return string|null
     */
    protected function getTemplateFile(): ?string
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

    /**
     * gets a TS Array by path.
     *
     * @param $typoscriptObjectPath
     *
     * @return array
     * @throws Exception
     */
    protected function getTsArrayByPath($typoscriptObjectPath): array
    {
        $pathSegments = GeneralUtility::trimExplode('.', $typoscriptObjectPath);
        $setup = $this->typoScriptSetup;
        foreach ($pathSegments as $segment) {
            if (!array_key_exists(($segment . '.'), $setup)) {
                throw new Exception(
                    'TypoScript object path "' . htmlspecialchars($typoscriptObjectPath) . '" does not exist',
                    1253191023
                );
            }
            $setup = $setup[$segment . '.'];
        }

        return $setup;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
