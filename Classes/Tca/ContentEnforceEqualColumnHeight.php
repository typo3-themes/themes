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

/**
 * Render a row for enforcing equal height of a column.
 */
class ContentEnforceEqualColumnHeight extends AbstractContentRow
{
    /**
     * Render a row for enforcing equal height of a column.
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
        // In case of new content elements, pid might be negative
        if ($pid < 1) {
            $pid = $this->getPidFromParentContentElement($pid);
        }
        // C-Type could be an array or a string
        if (is_array($cType) && isset($cType[0])) {
            $cType = $cType[0];
        }
        // Get values
        $values = explode(',', (string) $value);
        $valuesFlipped = array_flip($values);
        $valuesAvailable = [];

        // Get configuration
        $responsives = $this->getMergedConfiguration($pid, 'responsive', $cType);

        // Build checkboxes
        $checkboxes = '';
        if (!empty($responsives['properties'])) {
            foreach ($responsives['properties'] as $groupKey => $settings) {
                // Validate groupKey and get label
                $groupKey = substr((string) $groupKey, 0, -1);
                $label = $settings['label'] ?? $groupKey;
                $checkboxes .= '<div class="col-xs-6 col-sm-2 themes-column">' . PHP_EOL;
                $checkboxes .= '<label class="t3js-formengine-label">' . $this->getLanguageService()->sL(
                    $label
                ) . '</label>' . PHP_EOL;
                if (!empty($settings['rowSettings.'])) {
                    // check if theres already a value selected
                    $valueSet = false;
                    foreach ($settings['rowSettings.'] as $visibilityKey => $_) {
                        $tempKey = $groupKey . '-' . $visibilityKey;
                        if (!$valueSet) {
                            $valueSet = isset($valuesFlipped[$tempKey]);
                        }
                    }
                    foreach ($settings['rowSettings.'] as $visibilityKey => $visibilityLabel) {
                        $tempKey = $groupKey . '-' . $visibilityKey;
                        $valuesAvailable[] = $tempKey;
                        $checkboxes .= $this->getCheckbox(
                            $tempKey,
                            $tempKey,
                            $this->getLanguageService()->sL($visibilityLabel),
                            isset($valuesFlipped[$tempKey])
                        );
                    }
                }
                $checkboxes .= '</div>' . PHP_EOL;
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
        // Missed classes
        $missedField = $this->getMissedFields($values, $valuesAvailable);

        return ['html' => '<div class="contentEnforceEqualColumnHeight">' . $checkboxes . $hiddenField . $missedField . '</div>'];
    }
}
