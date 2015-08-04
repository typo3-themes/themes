<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 30.07.15
 * Time: 14:11
 */

namespace KayStrobach\Themes\Command;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\Flow\Utility\Files;

class ThemesCommandController extends CommandController {

	/**
	 * @param string $cssFile
	 * @param string $outputExtension
	 * @param string $path
	 * @throws \Exception
	 * @throws \TYPO3\Flow\Utility\Exception
	 */
	public function analyzeFontAweSomeCommand($cssFile, $outputExtension = '', $path = '') {;
		if ($outputExtension !== NULL) {
			$pageTsFile = ExtensionManagementUtility::extPath($outputExtension) . 'Configuration/PageTS/themes.icons.pagets';
			$setupTsFile = ExtensionManagementUtility::extPath($outputExtension) . 'Configuration/TypoScript/Library/lib.icons.cssMap.setupts';
			Files::createDirectoryRecursively(dirname($pageTsFile));
			Files::createDirectoryRecursively(dirname($setupTsFile));
		} elseif (file_exists($path)) {
			$pageTsFile = $path . '/themes.icons.pagets';
			$setupTsFile = $path . '/lib.icons.cssMap.setupts';
		} else {
			throw new \Exception('Please specify either an extension or an path where to store the icon files' . $path);
		}
		if (!is_file($cssFile)) {
			throw new \Exception('CssFile not found');
		}

		$cssFileContent = file_get_contents($cssFile);

		$pattern = '#\.(.*)-(.*):before#Ui';
		preg_match_all($pattern , $cssFileContent , $iconMatches);


		file_put_contents(
			$pageTsFile,
			$this->renderContent(
				ExtensionManagementUtility::extPath('themes') . 'Resources/Private/Templates/ThemesCommand/PageTs.txt',
				$iconMatches[2],
				'fa'
			)
		);

		file_put_contents(
			$setupTsFile,
			$this->renderContent(
				ExtensionManagementUtility::extPath('themes') . 'Resources/Private/Templates/ThemesCommand/SetupTs.txt',
				$iconMatches[2],
				'fa'
			)
		);

	}

	protected function renderContent($template, $icons, $prefix) {
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
		$view->setTemplatePathAndFilename($template);
		$view->assign('prefix', $prefix);
		$view->assign('icons', $icons);
		return $view->render();
	}
}