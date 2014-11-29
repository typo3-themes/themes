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
			$label = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:unavailable_classes');
			$missedField = '<span style="display:inline-block;color: #C00">' . $label . ': '. $missedClass . '</span>';
		}
		return $missedField;
	}

	protected function getMergedConfiguration($pid, $node, $cType) {

		// Get configuration ctype specific configuration
		$cTypeConfig = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.' . $node . '.' . $cType,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		$this->ctypeProperties = $cTypeConfig['properties'];

		// Get default configuration
		$defaultConfig = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.' . $node . '.default',
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		$this->defaultProperties = $defaultConfig['properties'];

		// Merge configurations
		$config = ArrayUtility::arrayMergeRecursiveOverrule($cTypeConfig, $defaultConfig);
		
		return $config;
	}
}