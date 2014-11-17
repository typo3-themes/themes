<?php

namespace KayStrobach\Themes\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Access constants
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
class ThemeEnabledConditionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {
	/**
	 * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
	 * @inject
	 */
	protected $themeRepository;

	/**
	 * @param string $theme
	 * @return string
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
	 */
	public function render($theme) {
		$pageId = intval(GeneralUtility::_GET('id'));
		$themeOfPage = $this->themeRepository->findByPageOrRootline($pageId);

		if (($themeOfPage !== NULL) && ($themeOfPage->getExtensionName() === $theme)) {
			return $this->renderThenChild();
		}
		return $this->renderElseChild();
	}
}