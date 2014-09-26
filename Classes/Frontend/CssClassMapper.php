<?php

namespace KayStrobach\Themes\Frontend;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EditorController
 *
 * @package KayStrobach\Themes\Frontend
 */
class CssClassMapper {

	public function mapGenericToFramework($content, $conf) {
		$genericClasses = array_flip(GeneralUtility::trimExplode(',', $content));
		$frameworkClasses = $conf['classMapping.'];
		$mappedClasses = array_intersect_key($frameworkClasses, $genericClasses);
		return implode(' ', $mappedClasses);
	}

}