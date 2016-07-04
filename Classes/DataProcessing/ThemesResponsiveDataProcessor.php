<?php
namespace KayStrobach\Themes\DataProcessing;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
/**
 * DataProcessor for Fluid Styled Content
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ThemesResponsiveDataProcessor implements DataProcessorInterface {

	/**
	 * @var array
	 */
	protected $setup;
	
	/**
	 * Process data for the Themes variants
	 *
	 * @param ContentObjectRenderer $cObj                       The content object renderer, which contains data of the content element
	 * @param array                 $contentObjectConfiguration The configuration of Content Object
	 * @param array                 $processorConfiguration     The configuration of this processor
	 * @param array                 $processedData              Key/value store of processed data (e.g. to be passed to a Fluid View)
	 * @return array the processed data as key/value store
	 * @throws ContentRenderingException
	 */
	public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData) {
		$keys = GeneralUtility::trimExplode(',', $processedData['data']['tx_themes_responsive'], TRUE);
		$processedData['themes']['responsive']['keys'] = $keys;
		$processedData['themes']['responsive']['css'] = array();
		if(!empty($keys)) {
			$setup = $this->getFrontendController()->tmpl->setup;
			foreach($keys as $key) {
				if(isset($setup['lib.']['content.']['cssMap.']['responsive.'][$key])) {
					// Special handling for column width
					if(strstr($key, '-column-width-')) {
						$keyParts = explode('-', $key);
						$columns = array_slice($keyParts, 3);
						if(!empty($columns)) {
							foreach($columns as $no=>$column) {
								$cssClass = trim($setup['lib.']['content.']['cssMap.']['responsive.'][$key]);
								$value = sprintf($cssClass, $column);
								$processedData['themes']['responsive']['column'][$no]['css'][$value] = $value;
								if(isset($processedData['themes']['responsive']['column'][$no]['css'])) {
									$processedData['themes']['responsive']['column'][$no]['cssClasses'] = implode(' ', $processedData['themes']['responsive']['column'][$no]['css']);
								}
							}
						}
					}
					else {
						$cssClass = trim($setup['lib.']['content.']['cssMap.']['responsive.'][$key]);
						if($cssClass !== '') {
							$processedData['themes']['responsive']['css'][$cssClass] = $cssClass;
						}
					}
				}
			}
			$processedData['themes']['responsive']['cssClasses'] = implode(' ', $processedData['themes']['responsive']['css']);
		}
		return $processedData;
	}
	
	/**
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected function getFrontendController() {
		return $GLOBALS['TSFE'];
	}

}
