<?php

namespace KayStrobach\Themes\ViewHelpers\Widget;

/**
 * Provides a Language Menu
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
class LanguageMenuViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @var \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController
	 * @inject
	 */
	protected $controller;

	/**
	 * @param \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController $controller
	 * @return void
	 */
	public function injectController(\KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController $controller) {
		$this->controller = $controller;
	}

	/**
	 * Language Repository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository
	 * @inject
	 */
	protected $languageRepository;

	/**
	 * @param \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository
	 * @return void
	 */
	public function injectLanguageRepository(\SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository) {
		$this->languageRepository = $languageRepository;
	}

	/**
	 * @return void
	 */
	public function initialize() {
		$this->controllerContext->getRequest()->setControllerExtensionName('Themes');
	}

	/**
	 * initialize the arguments of the viewHelper
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('availableLanguages', 'string', 'Commaseperated list of integers of the languages', FALSE, '');
		$this->registerArgument('currentLanguageUid', 'int', 'Id of the current language', FALSE, 0);
		$this->registerArgument('defaultLanguageIsoCodeShort', 'string', 'IsoCode of the default language', FALSE, 'en');
		$this->registerArgument('defaultLanguageLabel', 'string', 'Label of the default language', FALSE, 'English');
		$this->registerArgument('defaultLanguageFlag', 'string', 'Flag of the default language', FALSE, 'gb');
		$this->registerArgument('flagIconPath', 'string', 'directory containing the flags', FALSE, '/typo3/sysext/t3skin/images/flags/');
		$this->registerArgument('flagIconFileExtension', 'string', 'file extension of the flag files', FALSE, 'png');
	}

	/**
	 * @return string
	 */
	public function render() {
		try {
			return ($this->arguments['availableLanguages'] == '') ? '' : $this->initiateSubRequest();
		} catch(\TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException $e) {
			return 'ERROR: Problem loading image files, flag folder is missing ...';
		}
	}

}