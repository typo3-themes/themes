<?php

namespace KayStrobach\Themes\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Is a theme enabled?
 *
 * @author Thomas Deuling <typo3@coding.ms>, Kay Strobach
 * @package themes
 */
class ThemeEnabledConditionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * Initializes the "theme" argument.
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('theme', 'string', 'The theme');
	}

	/**
	 * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
	 *
	 * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
	 * @return bool
	 */
	protected static function evaluateCondition($arguments = null) {
		$pageId = intval(GeneralUtility::_GET('id'));
		/** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
		$themeRepository = GeneralUtility::makeInstance('KayStrobach\Themes\Domain\Repository\ThemeRepository');
		/** @var \KayStrobach\Themes\Domain\Model\Theme $themeOfPage */
		$themeOfPage = $themeRepository->findByPageOrRootline($pageId);
		return ($themeOfPage !== NULL) && ($themeOfPage->getExtensionName() === $arguments['theme']);
	}
	
}