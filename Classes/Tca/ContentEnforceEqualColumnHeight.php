<?php

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
     * @param array $parameters
     * @param mixed $parentObject
     *
     * @return string
     */
    public function renderField(array &$parameters, &$parentObject)
    {
        // Vars
        $uid = $parameters['row']['uid'];
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
        $values = explode(',', $value);
        $valuesFlipped = array_flip($values);
        $valuesAvailable = [];

        // Get configuration
        $responsives = $this->getMergedConfiguration($pid, 'responsive', $cType);

        // Build checkboxes
        $checkboxes = '';
        if (isset($responsives['properties']) && is_array($responsives['properties'])) {
            foreach ($responsives['properties'] as $groupKey => $settings) {

                // Validate groupKey and get label
                $groupKey = substr($groupKey, 0, -1);
                $label = isset($settings['label']) ? $settings['label'] : $groupKey;
                $checkboxes .= '<div class="col-xs-6 col-sm-2 themes-column">'.LF;
                $checkboxes .= '<label class="t3js-formengine-label">'.$this->getLanguageService()->sL($label).'</label>'.LF;
                if (isset($settings['rowSettings.']) && is_array($settings['rowSettings.'])) {

                    // check if theres already a value selected
                    $valueSet = false;
                    foreach ($settings['rowSettings.'] as $visibilityKey => $_) {
                        $tempKey = $groupKey.'-'.$visibilityKey;
                        if (!$valueSet) {
                            $valueSet = isset($valuesFlipped[$tempKey]);
                        }
                    }
                    foreach ($settings['rowSettings.'] as $visibilityKey => $visibilityLabel) {
                        $tempKey = $groupKey.'-'.$visibilityKey;
                        $valuesAvailable[] = $tempKey;
                        $checked = (isset($valuesFlipped[$tempKey])) ? 'checked="checked"' : '';

                        // build checkbox
                        $checkboxes .= '<div>'.LF;
                        $checkboxes .= '<label><input type="checkbox" name="'.$tempKey.'" value="'.$tempKey.'" '.$checked.'>'.LF;
                        $checkboxes .= $this->getLanguageService()->sL($visibilityLabel).'</label>'.LF;
                        $checkboxes .= '</div>'.LF;
                    }
                }
                $checkboxes .= '</div>'.LF;
            }
        }
        // Process current classes/identifiers
        $setClasses = array_intersect($values, $valuesAvailable);
        $setClass = htmlspecialchars(implode(' ', $setClasses));
        $setValue = htmlspecialchars(implode(',', $setClasses));
        // Allow admins to see the internal identifiers
        $inputType = 'hidden';
        if ($this->isAdmin()) {
            $inputType = 'text';
        }
        // Build hidden field structure
        $hiddenField = '<div>'.LF;
        $hiddenField .= '<div class="form-control-wrap">'.LF;
        $hiddenField .= '<input class="form-control themes-hidden-admin-field '.$setClass.'" ';
        $hiddenField .= 'readonly="readonly" type="'.$inputType.'" ';
        $hiddenField .= 'name="'.htmlspecialchars($name).'" ';
        $hiddenField .= 'value="'.$setValue.'" class="'.$setClass.'">'.LF;
        $hiddenField .= '</div>'.LF;
        $hiddenField .= '</div>'.LF;
        // Missed classes
        $missedField = $this->getMissedFields($values, $valuesAvailable);

        return '<div class="contentEnforceEqualColumnHeight">'.$checkboxes.$hiddenField.$missedField.'</div>';
    }
}
