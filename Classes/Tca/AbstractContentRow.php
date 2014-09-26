<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * Abstract for ContentRow
 *
 * @package KayStrobach\Themes\Tca
 */
abstract class AbstractContentRow {

	protected function getMissedFields($values, $valuesAvailable) {
		$missedField = '';
		$missedClasses = array_diff($values, $valuesAvailable);
		$missedClass = htmlspecialchars(implode(',', $missedClasses));
		if(!empty($missedClass)) {
			$missedField = '<span style="display:inline-block;color: #C00">Unavailable classes: '. $missedClass . '</span>';
		}
		return $missedField;
	}

	protected function getMergedConfiguration($pid, $node, $cType) {

		// Get configuration ctype specific configuration
		$cTypeConfig = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.' . $node . '.' . $cType,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);

		// Get default configuration
		$defaultConfig = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.' . $node . '.default',
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);

		// Merge configurations
		$config = ArrayUtility::arrayMergeRecursiveOverrule($cTypeConfig, $defaultConfig);
		
		return $config;
	}
}

?>