<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tca;

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

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Variant row.
 */
class ContentResponsive extends AbstractContentRow
{
    protected array $valuesFlipped = [];
    protected array $valuesAvailable = [];

    /**
     * Render a Content Variant row.
     *
     * @return string[]
     */
    public function render(): array
    {
        $parameters = $this->data['parameterArray'];
        $parameters['row'] = $this->data['databaseRow'];
        // Vars
        $pid = $parameters['row']['pid'];
        $name = $parameters['itemFormElName'];
        $value = $parameters['itemFormElValue'];
        $cType = $parameters['row']['CType'];
        $gridLayout = $parameters['row']['tx_gridelements_backend_layout'];
        // In case of new content elements, pid might be negative
        if ($pid < 1) {
            $pid = $this->getPidFromParentContentElement($pid);
        }
        // C-Type could be an array or a string
        if (is_array($cType) && isset($cType[0])) {
            $cType = $cType[0];
        }
        if (is_array($gridLayout) && isset($gridLayout[0])) {
            $gridLayout = $gridLayout[0];
        }
        // Get values
        $values = explode(',', (string)$value);
        $this->valuesFlipped = array_flip($values);
        $this->valuesAvailable = [];
        //
        // Get responsive settings
        $responsiveSettings = $this->getBeUser()->getTSConfig()['themes.']['content.']['responsive.']['settings.'] ?? null;
        if (isset($responsiveSettings['properties'])) {
            /** @var TypoScriptService $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $responsiveSettings = $typoScriptService->convertTypoScriptArrayToPlainArray(
                $responsiveSettings['properties']
            );
        } else {
            $responsiveSettings = [];
        }
        //
        // Get configuration
        $responsives = $this->getMergedConfiguration($pid, 'responsive', $cType, $gridLayout);
        //
        // Build select boxes
        $selectBoxes = '';
        if (!empty($responsives['properties'])) {
            foreach ($responsives['properties'] as $size => $settings) {
                //
                // Validate groupKey and get label
                $size = substr((string)$size, 0, -1);
                $label = $this->getLanguageService()->sL($settings['label'] ?? $size);
                //
                // is valid size?!
                if (isset($responsiveSettings['sizes']) && !isset($responsiveSettings['sizes'][$size])) {
                    continue;
                }
                $selectBoxes .= '<div class="col-md col-sm-6 col-xs-6 pb-3 themes-column">' . PHP_EOL;
                $selectBoxes .= '<label class="t3js-formengine-label mt-2">' . $label . '</label>' . PHP_EOL;
                foreach ($settings as $settingKey => $setting) {
                    if ($settingKey !== 'label') {
                        $selectBoxes .= $this->buildItem(
                            $size,
                            rtrim($settingKey, '.'),
                            $setting
                        );
                    }
                }
                $selectBoxes .= '</div>' . PHP_EOL;
            }
        }
        // Process current classes/identifiers
        $setClasses = array_intersect($values, $this->valuesAvailable);
        $setClass = htmlspecialchars(implode(' ', $setClasses), ENT_QUOTES | ENT_HTML5);
        $setValue = htmlspecialchars(implode(',', $setClasses), ENT_QUOTES | ENT_HTML5);
        // Allow admins to see the internal identifiers
        $inputType = 'hidden';
        if ($this->isAdminAndDebug()) {
            $inputType = 'text';
        }

        // Build hidden field structure
        $hiddenField = '<div>' . PHP_EOL;
        $hiddenField .= '<div class="form-control-wrap">' . PHP_EOL;
        $hiddenField .= '<input class="form-control themes-hidden-admin-field ' . $setClass . '" ';
        $hiddenField .= 'readonly="readonly" type="' . $inputType . '" ';
        $hiddenField .= 'name="' . htmlspecialchars((string)$name) . '" ';
        $hiddenField .= 'value="' . $setValue . '" class="' . $setClass . '">' . PHP_EOL;
        $hiddenField .= '</div>' . PHP_EOL;
        $hiddenField .= '</div>' . PHP_EOL;

        // Missed classes
        $missedField = $this->getMissedFields($values, $this->valuesAvailable);

        return ['html' => '<div class="contentResponsive row">' . $selectBoxes . $hiddenField . $missedField . '</div>'];
    }

    /**
     * @param string $size
     * @param string $groupKey
     * @param string[] $settings
     * @return string
     */
    protected function buildItem(string $size, string $groupKey, array $settings): string
    {
        $content = '<label class="t3js-formengine-label sub-label mt-2">';
        $content .= $this->getLanguageService()->sL($groupKey);
        $content .= '</label>' . PHP_EOL;
        $content .= '<select name="' . $size . '-' . $groupKey . '" class="form-select form-select-sm">' . PHP_EOL;
        $valueSet = false;
        foreach ($settings as $settingKey => $settingLabel) {
            $tempKey = $size . '-' . $groupKey . '-' . $settingKey;
            $this->valuesAvailable[] = $tempKey;
            // set the selected value
            if ($valueSet) {
                $selected = (isset($this->valuesFlipped[$tempKey])) ? 'selected="selected"' : '';
            } // set the default value, this means the first one
            else {
                $selected = 'selected="selected"';
                $valueSet = true;
            }
            $label = $this->getLanguageService()->sL($settingLabel);
            $content .= '<option value="' . $tempKey . '" ' . $selected . '>' . $label . '</option>' . PHP_EOL;
        }
        $content .= '</select>' . PHP_EOL;
        return $content;
    }
}
