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
class ThemesBehaviourDataProcessor implements DataProcessorInterface {
	/**
	 * Process data for the Themes behaviour
	 *
	 * @param ContentObjectRenderer $cObj                       The content object renderer, which contains data of the content element
	 * @param array                 $contentObjectConfiguration The configuration of Content Object
	 * @param array                 $processorConfiguration     The configuration of this processor
	 * @param array                 $processedData              Key/value store of processed data (e.g. to be passed to a Fluid View)
	 * @return array the processed data as key/value store
	 * @throws ContentRenderingException
	 */
	public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData) {
		$keys = GeneralUtility::trimExplode(',', $processedData['data']['tx_themes_behaviour'], TRUE);
		$processedData['themes']['behaviour']['css'] = array();
		$processedData['themes']['behaviour']['css2key'] = array();
		if(!empty($keys)) {
			$setup = $this->getFrontendController()->tmpl->setup;
			if(isset($setup['lib.']['content.']['cssMap.']['behaviour.']) && !empty($setup['lib.']['content.']['cssMap.']['behaviour.'])) {
				foreach($setup['lib.']['content.']['cssMap.']['behaviour.'] as $key => $cssClass) {
					if(is_array($cssClass)) {
						$key = substr($key, 0, -1);
						if(!empty($cssClass)) {
							foreach($cssClass as $subKey => $setting) {
								if(in_array($key . '-' . $subKey, $keys)) {
									$processedData['themes']['behaviour']['css'][$key] = $setting;
									$processedData['themes']['behaviour']['css2key'][$setting] = $key;
									break;
								}
							}
						}
					}
					else if(in_array($key, $keys)) {
						if($cssClass !== '') {
							$processedData['themes']['behaviour']['css'][$cssClass] = $cssClass;
							$processedData['themes']['behaviour']['css2key'][$cssClass] = $key;
						}
					}
				}
			}
			$processedData['themes']['behaviour']['key2css'] = array_flip($processedData['themes']['behaviour']['css2key']);
			$processedData['themes']['behaviour']['cssClasses'] = implode(' ', $processedData['themes']['behaviour']['css']);
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