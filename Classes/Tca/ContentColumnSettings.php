<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * Render a row for enforcing equal height of a column
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentColumnSettings extends AbstractContentRow {

	/**
	 * Render a row for enforcing equal height of a column
	 *
	 * @param array $parameters
	 * @param mixed $parentObject
	 * @return string
	 */
	public function renderField(array &$parameters, &$parentObject) {
		// Vars
		$uid   = $parameters['row']['uid'];
		$pid   = $parameters['row']['pid'];
		$name  = $parameters['itemFormElName'];
		$value = $parameters['itemFormElValue'];
		$cType = $parameters['row']['CType'];
		// In case of new content elements, pid might be negative
		if($pid<1) {
			$pid = $this->getPidFromParentContentElement($pid);
		}
		// C-Type could be an array or a string
		if(is_array($cType) && isset($cType[0])) {
			$cType = $cType[0];
		}
		// Get values
		$values = explode(',', $value);
		$valuesFlipped = array_flip($values);
		$valuesAvailable = array();

		// Get configuration
		$responsives = $this->getMergedConfiguration($pid, 'responsive', $cType);

		// Build selectboxes
		$selectboxes = '';
		if (isset($responsives['properties']) && is_array($responsives['properties'])) {
			foreach ($responsives['properties'] as $groupKey => $settings) {
				// Validate groupKey and get label
				$groupKey = substr($groupKey, 0, -1);
				$label = isset($settings['label']) ? $settings['label'] : $groupKey;
				$selectboxes .= '<div class="col-xs-6 col-sm-2 themes-column">' . LF;
				$selectboxes .= '<label class="t3js-formengine-label">' . $this->getLanguageService()->sL($label) . '</label>' . LF;
				if (isset($settings['columnSettings.']) && is_array($settings['columnSettings.'])) {
					foreach ($settings['columnSettings.'] as $visibilityKey => $visibilityLabel) {
						$start = $visibilityKey === 'width' ? 1 : 0;
						$tempKey = $groupKey . '-' . $visibilityKey;

						// Collect selectable values
						for ($i = $start; $i <= 12; $i++) {
							$valuesAvailable[] = $tempKey . '-' . $i;
						}

						// build radiobox
						$selectboxes .= '<div>' . LF;
						$selectboxes .= '<label class="themes-select-label">' . $this->getLanguageService()->sL($visibilityLabel) . '</label>' . LF;
						$selectboxes .= '<select class="form-control form-control-adapt input-sm" name="' . $tempKey . '">' . LF;
						$selectboxes .= '<option value="">default</option>' . LF;
						for ($i = $start; $i <= 12; $i++) {
							// set the selected value
							$selected = (isset($valuesFlipped[$tempKey . '-' . $i])) ? 'selected="selected"' : '';
							$selectboxes .= '<option value="' . $tempKey . '-' . $i . '" ' . $selected .  '>' . $i . ' columns of 12<!-- '  . $visibilityKey . ' ' . $i . '--></option>' . LF;
						}
						$selectboxes .= '</select>' . LF;
						$selectboxes .= '</div>' . LF;
					}
				}
				$selectboxes .= '</div>' . LF;
			}
		}
		// Process current classes/identifiers
		$setClasses = array_intersect($values, $valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));
		// Allow admins to see the internal identifiers
		$inputType = 'hidden';
		if($this->isAdmin()) {
			$inputType = 'text';
		}
		// Build hidden field structure
		$hiddenField = '<div class="t3js-formengine-field-item">' . LF;
		$hiddenField .= '<div class="form-control-wrap">' . LF;
		$hiddenField .= '<input class="form-control themes-hidden-admin-field ' . $setClass . '" ';
		$hiddenField .= 'readonly="readonly" type="' . $inputType . '" ';
		$hiddenField .= 'name="' . htmlspecialchars($name) . '" ';
		$hiddenField .= 'value="' . $setValue . '" class="' . $setClass . '">' . LF;
		$hiddenField .= '</div>' . LF;
		$hiddenField .= '</div>' . LF;
		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);
		return '<div class="contentColumnSettings">' . $selectboxes . $hiddenField . $missedField . '</div>';
	}
}