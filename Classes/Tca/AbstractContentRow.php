<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * Abstract for ContentRow
 *
 * @package KayStrobach\Themes\Tca
 */
abstract class AbstractContentRow {

	protected $ctypeProperties = array();
	protected $defaultProperties = array();

	protected function getMissedFields($values, $valuesAvailable) {
		$missedField = '';
		$missedClasses = array_diff($values, $valuesAvailable);
		$missedClass = htmlspecialchars(implode(', ', $missedClasses));
		if (!empty($missedClass)) {
			$label = $this->getLanguageService()->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:unavailable_classes');
			$missedField = '<div class="alert alert-danger" role="alert"><strong>' . $label . ':</strong> '. $missedClass . '</div>';
		}
		return $missedField;
	}

	protected function getMergedConfiguration($pid, $node, $cType) {
		// Get configuration ctype specific configuration
		$cTypeConfig = $this->getBeUser()->getTSConfig(
			'themes.content.' . $node . '.' . $cType,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		$this->ctypeProperties = $cTypeConfig['properties'];
		// Get default configuration
		$defaultConfig = $this->getBeUser()->getTSConfig(
			'themes.content.' . $node . '.default',
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		$this->defaultProperties = $defaultConfig['properties'];
		// Merge configurations
		$config = ArrayUtility::arrayMergeRecursiveOverrule($cTypeConfig, $defaultConfig);
		return $config;
	}

	/**
	 * Checks if a backend user is an admin user
	 * @return boolean
	 */
	protected function isAdmin() {
		return $this->getBeUser()->isAdmin();
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBeUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}
}