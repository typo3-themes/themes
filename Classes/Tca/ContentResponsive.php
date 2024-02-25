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
        $values = explode(',', (string) $value);
        $valuesFlipped = array_flip($values);
        $valuesAvailable = [];

        // Get responsive settings
        $responsiveSettings = $this->getBeUser()->getTSConfig(
        )['themes.']['content.']['responsive.']['settings.'] ?? null;
        $cssStyles = '';
        $cssClasses = 'themes-column';
        if (isset($responsiveSettings['properties'])) {
            /** @var TypoScriptService $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $responsiveSettings = $typoScriptService->convertTypoScriptArrayToPlainArray(
                $responsiveSettings['properties']
            );
            if ((is_countable($responsiveSettings['sizes']) ? count($responsiveSettings['sizes']) : 0) > 0) {
                $cssStyles = 'width: ' . (100 / (is_countable($responsiveSettings['sizes']) ? count(
                    $responsiveSettings['sizes']
                ) : 0) - 1) . '%; float:left;margin-left:0.5%;margin-right:0.5%;margin-bottom:8px;border:none';
            }
        } else {
            $responsiveSettings = [];
        }
        if ($cssStyles === '') {
            $cssClasses = 'col-xs-6 col-sm-2 themes-column';
        }

        // Get configuration
        $responsives = $this->getMergedConfiguration($pid, 'responsive', $cType);
        // Build select boxes
        $selectboxes = '';
        if (!empty($responsives['properties'])) {
            foreach ($responsives['properties'] as $groupKey => $settings) {
                // Validate groupKey and get label
                $groupKey = substr((string) $groupKey, 0, -1);
                $label = $settings['label'] ?? $groupKey;

                // is valid size?!
                if (isset($responsiveSettings['sizes']) && !isset($responsiveSettings['sizes'][$groupKey])) {
                    continue;
                }

                $selectboxes .= '<div class="' . $cssClasses . '" style="' . $cssStyles . '">' . PHP_EOL;
                $selectboxes .= '<label class="t3js-formengine-label">' . $this->getLanguageService()->sL(
                    $label
                ) . '</label>' . PHP_EOL;
                if (!empty($settings['visibility.'])) {
                    // check if there's already a value selected
                    $valueSet = false;
                    foreach ($settings['visibility.'] as $visibilityKey => $_) {
                        $tempKey = $groupKey . '-' . $visibilityKey;
                        if (!$valueSet) {
                            $valueSet = isset($valuesFlipped[$tempKey]);
                        }
                    }
                    $selectboxes .= '<label class="t3js-formengine-label sub-label" style="font-weight:normal">' . $this->getLanguageService(
                    )->sL('visibility') . '</label>' . PHP_EOL;

                    $selectbox = '<select name="' . $groupKey . '" class="form-select form-select-sm">' . PHP_EOL;
                    foreach ($settings['visibility.'] as $visibilityKey => $visibilityLabel) {
                        $tempKey = $groupKey . '-' . $visibilityKey;
                        $valuesAvailable[] = $tempKey;
                        // set the selected value
                        if ($valueSet) {
                            $selected = (isset($valuesFlipped[$tempKey])) ? 'selected="selected"':'';
                        } // set the default value, this means the first one
                        else {
                            $selected = 'selected="selected"';
                            $valueSet = true;
                        }
                        $label = $this->getLanguageService()->sL($visibilityLabel);
                        $selectbox .= '<option value="' . $tempKey . '" ' . $selected . '>' . $label . '</option>' . PHP_EOL;
                    }
                    $selectbox .= '</select>' . PHP_EOL;
                    $selectboxes .= $selectbox;
                }
                $selectboxes .= '</div>' . PHP_EOL;
            }

            // For special content elements
            if ($cType !== 'gridelements_pi1' && isset($responsives['properties'][$cType . '.'])) {
                $tempContent = [];
                $valueSet = false;
                foreach ($responsives['properties'][$cType . '.'] as $groupKey => $settings) {
                    $groupKey = substr((string) $groupKey, 0, -1);
                    if (!empty($settings)) {
                        foreach ($settings as $settingKey => $settingValues) {
                            $settingKey = substr((string) $settingKey, 0, -1);
                            $tempContent[$settingKey] .= '<div class="' . $cssClasses . '" style="' . $cssStyles . '">' . PHP_EOL;
                            $tempContent[$settingKey] .= '<label class="t3js-formengine-label sub-label">' . $this->getLanguageService(
                            )->sL($settingKey) . '</label>' . PHP_EOL;
                            $selectbox = '<select name="' . $groupKey . '-' . $settingKey . '" class="form-select form-select-sm">' . PHP_EOL;
                            foreach ($settingValues as $settingEntryKey => $settingEntryLabel) {
                                $tempKey = $groupKey . '-' . $settingKey . '-' . $settingEntryKey;
                                $valuesAvailable[] = $tempKey;
                                // set the selected value
                                if ($valueSet) {
                                    $selected = (isset($valuesFlipped[$tempKey])) ? 'selected="selected"':'';
                                } // set the default value, this means the first one
                                else {
                                    $selected = 'selected="selected"';
                                    $valueSet = true;
                                }
                                $label = $this->getLanguageService()->sL($settingEntryLabel);
                                $selectbox .= '<option value="' . $tempKey . '" ' . $selected . '>' . $label . '</option>' . PHP_EOL;
                            }
                            $selectbox .= '</select>' . PHP_EOL;
                            $tempContent[$settingKey] .= $selectbox;
                            $tempContent[$settingKey] .= '</div>' . PHP_EOL;
                        }
                    }
                }
                $selectboxes .= implode('', $tempContent);
            }

            // For special grid elements
            if ($cType === 'gridelements_pi1' && isset($responsives['properties'][$gridLayout . '.'])) {
                $tempContent = [];
                $valueSet = false;
                foreach ($responsives['properties'][$gridLayout . '.'] as $groupKey => $settings) {
                    $groupKey = substr((string) $groupKey, 0, -1);
                    $tempContent[$groupKey] = '';
                    if (!empty($settings)) {
                        foreach ($settings as $settingKey => $settingValues) {
                            $settingKey = substr((string) $settingKey, 0, -1);
                            if (!array_key_exists($settingKey, $tempContent)) {
                                $tempContent[$settingKey] = '';
                            }
                            $tempContent[$settingKey] .= '<div class="' . $cssClasses . '" style="' . $cssStyles . '">' . PHP_EOL;
                            $tempContent[$settingKey] .= '<label class="t3js-formengine-label sub-label">' . $this->getLanguageService(
                            )->sL($settingKey) . '</label>' . PHP_EOL;
                            $selectbox = '<select name="' . $groupKey . '-' . $settingKey . '" class="form-select form-select-sm">' . PHP_EOL;
                            foreach ($settingValues as $settingEntryKey => $settingEntryLabel) {
                                $tempKey = $groupKey . '-' . $settingKey . '-' . $settingEntryKey;
                                $valuesAvailable[] = $tempKey;
                                // set the selected value
                                if ($valueSet) {
                                    $selected = (isset($valuesFlipped[$tempKey])) ? 'selected="selected"':'';
                                } // set the default value, this means the first one
                                else {
                                    $selected = 'selected="selected"';
                                    $valueSet = true;
                                }
                                $label = $this->getLanguageService()->sL($settingEntryLabel);
                                $selectbox .= '<option value="' . $tempKey . '" ' . $selected . '>' . $label . '</option>' . PHP_EOL;
                            }
                            $selectbox .= '</select>' . PHP_EOL;
                            $tempContent[$settingKey] .= $selectbox;
                            $tempContent[$settingKey] .= '</div>' . PHP_EOL;
                        }
                    }
                }
                $selectboxes .= implode('', $tempContent);
            }
        }
        // Process current classes/identifiers
        $setClasses = array_intersect($values, $valuesAvailable);
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
        $hiddenField .= 'name="' . htmlspecialchars((string) $name) . '" ';
        $hiddenField .= 'value="' . $setValue . '" class="' . $setClass . '">' . PHP_EOL;
        $hiddenField .= '</div>' . PHP_EOL;
        $hiddenField .= '</div>' . PHP_EOL;

        // Build hidden field structure
        //  themes-hidden-admin-field
        //$hiddenField = '<input readonly="readonly" type="' . $inputType . '" name="' . htmlspecialchars($name) . '" value="' . $setValue . '"  class="' . $setClass . '">' . PHP_EOL;

        // Missed classes
        $missedField = $this->getMissedFields($values, $valuesAvailable);

        return ['html' => '<div class="contentResponsive row">' . $selectboxes . $hiddenField . $missedField . '</div>'];
    }
}
