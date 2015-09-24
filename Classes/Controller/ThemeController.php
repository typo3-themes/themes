<?php

namespace KayStrobach\Themes\Controller;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Utilities\FindParentPageWithThemeUtility;
use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class ThemeController
 *
 * @package KayStrobach\Themes\Controller
 */
class ThemeController extends ActionController {

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
	 * @inject
	 */
	protected $themeRepository;

	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->typoScriptSetup = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
	}

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->themeRepository = new \KayStrobach\Themes\Domain\Repository\ThemeRepository();
	}

	/**
	 * renders the given theme
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->templateName = $this->evaluateTypoScript('plugin.tx_themes.settings.templateName');
		$templateFile = $this->getTemplateFile();
		if ($templateFile !== NULL) {
			$this->view->setTemplatePathAndFilename($templateFile);
		}
		$this->view->assign('templateName', $this->templateName);
		$this->view->assign('theme', $this->themeRepository->findByPageOrRootline($GLOBALS['TSFE']->id));
		$this->view->assign('page', $this->pageRepository->getPage($GLOBALS['TSFE']->id));
		$this->view->assign('data', $this->pageRepository->getPage($GLOBALS['TSFE']->id));
		$this->view->assign('TSFE', $GLOBALS['TSFE']);
	}

	/**
	 * renders a given TypoScript Path
	 *
	 * @param $path
	 * @return string
	 */
	protected function evaluateTypoScript($path) {
		/** @var \TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper $vh */
		$vh = $this->objectManager->get('TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper');
		$vh->setRenderChildrenClosure(function() {
			return '';
		});
		return $vh->render($path);
	}

	/**
	 * gets a TS Array by path
	 *
	 * @param $typoscriptObjectPath
	 * @return array
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	protected function getTsArrayByPath($typoscriptObjectPath) {
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
	 * get the needed templateFile from TS
	 *
	 * @return null|string
	 */
	protected function getTemplateFile() {
		$templatePaths = $this->getTsArrayByPath('plugin.tx_themes.view.templateRootPaths');
		krsort($templatePaths);
		foreach ($templatePaths as $templatePath) {
			$cleanedPath = GeneralUtility::getFileAbsFileName($templatePath) . 'Theme/' . $this->templateName . '.html';
			if (is_file($cleanedPath)) {
				return $cleanedPath;
			}
		}
		return NULL;
	}

}
