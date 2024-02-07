<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tca;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class ContentColumnSettings extends AbstractContentRow
{
    /**
     * Container objects give $nodeFactory down to other containers.
     *
     * @param NodeFactory|null $nodeFactory
     * @param array|null $data
     */
    public function __construct(NodeFactory $nodeFactory = null, array $data = null)
    {
        if ($nodeFactory !== null) {
            parent::__construct($nodeFactory, $data);
        }
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Render a row for enforcing equal height of a column.
     *
     * @return array
     * @throws DBALException
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

        // Build selectboxes
        $selectboxes = '';
        if (!empty($responsives['properties'])) {
            foreach ($responsives['properties'] as $groupKey => $settings) {
                // Validate groupKey and get label
                $groupKey = substr((string) $groupKey, 0, -1);
                $label = $settings['label'] ?? $groupKey;
                $selectboxes .= '<div class="col-xs-6 col-sm-2 themes-column">' . PHP_EOL;
                $selectboxes .= '<label class="t3js-formengine-label">' . $this->getLanguageService()->sL(
                    $label
                ) . '</label>' . PHP_EOL;
                if (!empty($settings['columnSettings.'])) {
                    foreach ($settings['columnSettings.'] as $visibilityKey => $visibilityLabel) {
                        $start = $visibilityKey === 'width' ? 1:0;
                        $tempKey = $groupKey . '-' . $visibilityKey;

                        // Collect selectable values
                        for ($i = $start; $i <= 12; $i++) {
                            $valuesAvailable[] = $tempKey . '-' . $i;
                        }

                        // build radiobox
                        $selectboxes .= '<div>' . PHP_EOL;
                        $selectboxes .= '<label class="themes-select-label">' . $this->getLanguageService()->sL(
                            $visibilityLabel
                        ) . '</label>' . PHP_EOL;
                        $selectboxes .= '<select class="form-control form-control-adapt input-sm" name="' . $tempKey . '">' . PHP_EOL;
                        $selectboxes .= '<option value="">default</option>' . PHP_EOL;
                        for ($i = $start; $i <= 12; $i++) {
                            // set the selected value
                            $selected = (isset($valuesFlipped[$tempKey . '-' . $i])) ? 'selected="selected"':'';
                            $selectboxes .= '<option value="' . $tempKey . '-' . $i . '" ' . $selected . '>' . $i . ' columns of 12<!-- ' . $visibilityKey . ' ' . $i . '--></option>' . PHP_EOL;
                        }
                        $selectboxes .= '</select>' . PHP_EOL;
                        $selectboxes .= '</div>' . PHP_EOL;
                    }
                }
                $selectboxes .= '</div>' . PHP_EOL;
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

        return ['html' => '<div class="contentColumnSettings">' . $selectboxes . $hiddenField . $missedField . '</div>'];
    }
}
