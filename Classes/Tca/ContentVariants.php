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
 * Render a Content Variant row.
 */
class ContentVariants extends AbstractContentRow
{
    protected $checkboxesArray = [];
    protected $valuesFlipped = [];
    protected $valuesAvailable = [];

    /**
     * Render a Content Variant row.
     *
     * @return array
     */
    public function render()
    {
        $parameters = $this->data['parameterArray'];
        $parameters['row'] = $this->data['databaseRow'];
        // Vars
        $uid = $parameters['row']['uid'];
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
        $values = explode(',', $value);
        $this->valuesFlipped = array_flip($values);
        $this->valuesAvailable = [];
        // Get configuration
        $variants = $this->getMergedConfiguration($pid, 'variants', $cType);

        // Build checkboxes
        $this->checkboxesArray['default'] = [];
        $this->checkboxesArray['ctype'] = [];
        $this->checkboxesArray['gridLayout'] = [];
        if (isset($variants['properties']) && is_array($variants['properties'])) {
            foreach ($variants['properties'] as $contentElementKey => $label) {
                // GridElements: are able to provide grid-specific variants
                if (is_array($label) && $cType === 'gridelements_pi1' && !array_key_exists($contentElementKey, $this->defaultProperties)) {
                    $contentElementKey = substr($contentElementKey, 0, -1);

                    // Variant for all GridElements
                    if ($contentElementKey == 'default' && !empty($label)) {
                        foreach ($label as $gridLayoutKey => $gridLayoutVariantLabel) {
                            $this->createElement($gridLayoutKey, $gridLayoutVariantLabel, 'ctype');
                        }
                    }
                    // Variant only for selected GridElement
                    elseif ($contentElementKey == $gridLayout && !empty($label)) {
                        foreach ($label as $gridLayoutKey => $gridLayoutVariantLabel) {
                            $this->createElement($gridLayoutKey, $gridLayoutVariantLabel, 'gridLayout');
                        }
                    }
                }
                // Normal CEs
                else {
                    // Is default property!?
                    if (array_key_exists($contentElementKey, $this->defaultProperties)) {
                        $this->createElement($contentElementKey, $label, 'default');
                    }
                    // Is ctype specific!
                    else {
                        $this->createElement($contentElementKey, $label, 'ctype');
                    }
                }
            }
        }
        // Merge checkbox groups
        $checkboxes = '';
        $checkboxes .= $this->getMergedCheckboxes('default');
        $checkboxes .= $this->getMergedCheckboxes('ctype', $cType);
        $checkboxes .= $this->getMergedCheckboxes('gridLayout', $cType.'/'.$gridLayout);
        if ($checkboxes === '') {
            $checkboxes = $this->getLanguageService()->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.no_variants_available');
        }
        // Process current classes/identifiers
        $setClasses = array_intersect($values, $this->valuesAvailable);
        $setClass = htmlspecialchars(implode(' ', $setClasses));
        $setValue = htmlspecialchars(implode(',', $setClasses));
        // Allow admins to see the internal identifiers
        $inputType = 'hidden';
        if ($this->isAdminAndDebug()) {
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
        $missedField = $this->getMissedFields($values, $this->valuesAvailable);

        return ['html' => '<div class="contentVariant">'.$checkboxes.$hiddenField.$missedField.'</div>'];
    }

    /**
     * Creates a checkbox/select box.
     *
     * @param $key \string Key/name of the element
     * @param $label \string|\array Label of the element
     * @param $type \string Type of the element property
     */
    protected function createElement($key, $label, $type)
    {
        if (is_array($label) && !empty($label)) {
            $this->createSelectbox($key, $label, $type);
        } elseif (is_string($label)) {
            $this->createCheckbox($key, $label, $type);
        }
    }

    /**
     * Creates a checkbox.
     *
     * @param $key \string Key/name of the checkbox
     * @param $label \string Label of the checkbox
     * @param $type \string Type of the checkbox property
     */
    protected function createCheckbox($key, $label, $type)
    {
        $label = $this->getLanguageService()->sL($label);
        $this->valuesAvailable[] = $key;
        $checked = (isset($this->valuesFlipped[$key])) ? 'checked="checked"' : '';
        $checkbox = '<div  class="col-xs-12 col-sm-4 col-md-3 col-lg-2">'.LF;
        $checkbox .= '<label class="themes-checkbox-label" title="'.$label.'">'.LF;
        $checkbox .= '<input type="checkbox" name="'.$key.'" '.$checked.'>'.LF;
        $checkbox .= $this->getLanguageService()->sL($label).'</label>'.LF;
        $checkbox .= '</div>'.LF;
        $this->checkboxesArray[$type][] = $checkbox;
    }

    /**
     * Creates a select box.
     *
     * @param $key \string Key/name of the select box
     * @param $label \array Array with items of the select box
     * @param $type \string Type of the select box property
     */
    protected function createSelectbox($key, $items, $type)
    {
        // Remove dot
        $key = substr($key, 0, -1);
        $selectbox = '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">'.LF;
        $selectbox .= '<div class="form-control-wrap">'.LF;
        $selectbox .= '<select name="'.$key.'" class="form-control form-control-adapt input-sm">'.LF;
        $activeKey = '';
        foreach ($items as $itemKey => $itemValue) {
            if ($activeKey == '') {
                $activeKey = $key.'-'.$itemKey;
            }
            $selected = '';
            if (isset($this->valuesFlipped[$key.'-'.$itemKey])) {
                $activeKey = $key.'-'.$itemKey;
                $selected = 'selected="selected"';
            }
            $label = $this->getLanguageService()->sL($itemValue);
            $selectbox .= '<option value="'.$key.'-'.$itemKey.'" '.$selected.'>'.$label.'</option>'.LF;
        }
        $selectbox .= '</select>'.LF;
        $selectbox .= '</div>'.LF;
        $selectbox .= '</div>'.LF;
        $this->valuesAvailable[] = $activeKey;
        $this->checkboxesArray[$type][] = $selectbox;
    }

    /**
     * Merge checkboxes into a group.
     *
     * @param $type \string Type of the checkbox property
     *
     * @return string Grouped checkboxes
     */
    protected function getMergedCheckboxes($type, $labelInfo = '')
    {
        $checkboxes = '';
        if (!empty($this->checkboxesArray[$type])) {
            $labelKey = 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.'.strtolower($type).'_group_label';
            $label = $this->getLanguageService()->sL($labelKey);
            if (trim($labelInfo) != '') {
                $label .= '('.$labelInfo.')';
            }
            $checkboxes .= '<label class="t3js-formengine-label themes-label-'.$type.' col-xs-12">'.$label.':</label>';
            $checkboxes .= implode('', $this->checkboxesArray[$type]).LF;
        }

        return $checkboxes;
    }
}
