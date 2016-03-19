<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Variant row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentResponsive extends AbstractContentRow {

	/**
	 * Render a Content Variant row
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

		// Build checkboxes
		$radiobuttons = '';
		if (isset($responsives['properties']) && is_array($responsives['properties'])) {
			foreach ($responsives['properties'] as $groupKey => $settings) {

				// Validate groupKey and get label
				$groupKey = substr($groupKey, 0, -1);
				$label = isset($settings['label']) ? $settings['label'] : $groupKey;

				$radiobuttons .= '<div class="col-xs-6 col-sm-2 themes-column">' . LF;
				//$radiobuttons .= '<div>' . LF;
				$radiobuttons .= '<label class="t3js-formengine-label">' . $this->getLanguageService()->sL($label) . '</label>' . LF;
				//$radiobuttons .= '</div>' . LF;
				if (isset($settings['visibility.']) && is_array($settings['visibility.'])) {

					// check if theres already a value selected
					$valueSet = FALSE;
					foreach ($settings['visibility.'] as $visibilityKey => $_) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						if (!$valueSet) {
							$valueSet = isset($valuesFlipped[$tempKey]);
						}
					}

					foreach ($settings['visibility.'] as $visibilityKey => $visibilityLabel) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						$valuesAvailable[] = $tempKey;

						// set the selected value
						if ($valueSet) {
							$selected = (isset($valuesFlipped[$tempKey])) ? 'checked="checked"' : '';
						}
						// set the default value, this means the first one
						else {
							$selected = 'checked="checked"';
							$valueSet = TRUE;
						}

						// build radiobox
						//$radiobuttons .= '<div>' . LF;
						$radiobuttons .= '<label><input type="radio" name="' . $groupKey . '" value="' . $tempKey . '" ' . $selected . ' />' . LF;
						$radiobuttons .= $this->getLanguageService()->sL($visibilityLabel) . '</label>' . LF;
						//$radiobuttons .= '</div>' . LF;
					}
				}
				$radiobuttons .= '</div>' . LF;

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
		
		
		// Build hidden field structure
		//  themes-hidden-admin-field
		//$hiddenField = '<input readonly="readonly" type="' . $inputType . '" name="' . htmlspecialchars($name) . '" value="' . $setValue . '"  class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);

		return '<div class="contentResponsive">' . $radiobuttons . $hiddenField . $missedField . '</div>';
	}
}