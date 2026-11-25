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

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
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
     * @var ThemeRepository
     */
    protected ThemeRepository $themeRepository;

    /**
     * @var PageRepository
     */
    protected PageRepository $pageRepository;

    /**
     * Without the settings of ActionController::injectConfigurationManager(): plugin.tx_themes.settings
     * is no array in every theme, the action reads the framework configuration itself.
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        // TYPO3 v14: the property has no default any more, resolveView() reads it
        $this->settings = [];
        // like the parent method
        $this->arguments = GeneralUtility::makeInstance(Arguments::class);
    }

    public function __construct(ThemeRepository $themeRepository, PageRepository $pageRepository)
    {
        $this->themeRepository = $themeRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * renders the given theme.
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $this->templateName = $this->evaluateTypoScript('plugin.tx_themes.view.templateName');
        $templateFile = $this->getTemplateFile();
        if ($templateFile !== null) {
            // the view of TYPO3 v13+ (FluidViewAdapter) has no setTemplatePathAndFilename(), the rendering context
            // exists in TYPO3 v12 as well
            $this->view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templateFile);
        }
        $this->view->assign('templateName', $this->templateName);

        $pageId = $this->getPageId();
        $this->view->assign('theme', $this->themeRepository->findByPageOrRootline($pageId));
        // Get page data
        $pageArray = $this->pageRepository->getPage($pageId);
        $pageArray['icon'] = '';
        // Map page icon
        if (isset($pageArray['tx_themes_icon']) && $pageArray['tx_themes_icon'] != '') {
            $setup = $this->getTypoScriptSetup();
            $pageArray['icon'] = $setup['lib.']['icons.']['cssMap.'][$pageArray['tx_themes_icon']] ?? '';
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
        // TYPO3 v14 has no TSFE any more
        $this->view->assign('TSFE', $GLOBALS['TSFE'] ?? null);
        return $this->htmlResponse();
    }

    /**
     * renders a given TypoScript Path.
     *
     *
     * @param string $path
     * @return string
     */
    protected function evaluateTypoScript(string $path): string
    {
        $setup = $this->getTypoScriptSetup();
        $pathSegments = GeneralUtility::trimExplode('.', $path, true);
        $objectName = (string)array_pop($pathSegments);
        foreach ($pathSegments as $segment) {
            $setup = $setup[$segment . '.'] ?? [];
        }
        $contentObject = $this->request->getAttribute('currentContentObject');
        if (!$contentObject instanceof ContentObjectRenderer) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObject->setRequest($this->request);
        }
        return (string)$contentObject->cObjGetSingle((string)($setup[$objectName] ?? ''), $setup[$objectName . '.'] ?? []);
    }

    /**
     * @return array<string, mixed> the TypoScript setup of the current page
     */
    protected function getTypoScriptSetup(): array
    {
        $frontendTypoScript = $this->request->getAttribute('frontend.typoscript');
        return $frontendTypoScript !== null ? $frontendTypoScript->getSetupArray() : [];
    }

    protected function getPageId(): int
    {
        $routing = $this->request->getAttribute('routing');
        if ($routing instanceof PageArguments) {
            return $routing->getPageId();
        }
        return (int)($GLOBALS['TSFE']->id ?? 0);
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
        $setup = $this->getTypoScriptSetup();
        foreach ($pathSegments as $segment) {
            if (!array_key_exists(($segment . '.'), $setup)) {
                throw new Exception(
                    'TypoScript object path "' . htmlspecialchars((string) $typoscriptObjectPath) . '" does not exist',
                    1253191023
                );
            }
            $setup = $setup[$segment . '.'];
        }

        return $setup;
    }
}
