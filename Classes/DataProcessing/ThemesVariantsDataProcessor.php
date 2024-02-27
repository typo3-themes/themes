<?php

declare(strict_types=1);

namespace KayStrobach\Themes\DataProcessing;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * DataProcessor for Fluid Styled Content.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ThemesVariantsDataProcessor implements DataProcessorInterface
{
    /**
     * Process data for the Themes variants.
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $keys = GeneralUtility::trimExplode(
            ',',
            $processedData['data']['tx_themes_variants'] ?? '',
            true
        );
        $processedData['themes']['variants']['css'] = [];
        $processedData['themes']['variants']['css2key'] = [];
        if (!empty($keys)) {
            $setup = $this->getFrontendController()->tmpl->setup;
            if (isset($setup['lib.']['content.']['cssMap.']['variants.']) && !empty($setup['lib.']['content.']['cssMap.']['variants.'])) {
                foreach ($setup['lib.']['content.']['cssMap.']['variants.'] as $key => $cssClass) {
                    if (is_array($cssClass)) {
                        $key = substr((string) $key, 0, -1);
                        if (!empty($cssClass)) {
                            foreach ($cssClass as $subKey => $setting) {
                                if (in_array($key . '-' . $subKey, $keys)) {
                                    $processedData['themes']['variants']['css'][$key] = $setting;
                                    $processedData['themes']['variants']['css2key'][$setting] = $key;
                                    break;
                                }
                            }
                        }
                    } elseif (in_array($key, $keys)) {
                        if ($cssClass !== '') {
                            $processedData['themes']['variants']['css'][$cssClass] = $cssClass;
                            $processedData['themes']['variants']['css2key'][$cssClass] = $key;
                        }
                    }
                }
            }
            $processedData['themes']['variants']['key2css'] = array_flip(
                $processedData['themes']['variants']['css2key']
            );
            $processedData['themes']['variants']['cssClasses'] = implode(
                ' ',
                $processedData['themes']['variants']['css']
            );
        }
        return $processedData;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
