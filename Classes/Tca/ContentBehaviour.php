<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Behaviour row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentBehaviour extends AbstractContentRow {

	protected $checkboxesArray = array();
	protected $valuesFlipped = array();
	protected $valuesAvailable = array();

	/**
	 * Render a Content Behaviour row
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
		$gridLayout = $parameters['row']['tx_gridelements_backend_layout'];
		// C-Type could be an array or a string
		if(is_array($cType) && isset($cType[0])) {
			$cType = $cType[0];
		}
		if(is_array($gridLayout) && isset($gridLayout[0])) {
			$gridLayout = $gridLayout[0];
		}
		// Get values
		$values = explode(',', $value);
		$this->valuesFlipped = array_flip($values);
		$this->valuesAvailable = array();
		// Get configuration
		$behaviours = $this->getMergedConfiguration($pid, 'behaviour', $cType);

		// Build checkboxes
		$this->checkboxesArray['default'] = array();
		$this->checkboxesArray['ctype'] = array();
		$this->checkboxesArray['gridLayout'] = array();
		if (isset($behaviours['properties']) && is_array($behaviours['properties'])) {

			foreach ($behaviours['properties'] as $contentElementKey => $label) {

				// GridElements: are able to provide grid-specific behaviours
				if (is_array($label) && $cType === 'gridelements_pi1') {
					$contentElementKey = substr($contentElementKey, 0, -1);

					// Behaviour for all GridElements
					if ($contentElementKey == 'default' && !empty($label)) {
						foreach ($label as $gridLayoutKey => $gridLayoutBehaviourLabel) {
							$this->createCheckbox($gridLayoutKey, $gridLayoutBehaviourLabel, 'ctype');
						}
					}
					// Behaviour only for selected GridElement
					else if ($contentElementKey == $gridLayout && !empty($label)) {
						foreach ($label as $gridLayoutKey => $gridLayoutBehaviourLabel) {
							$this->createCheckbox($gridLayoutKey, $gridLayoutBehaviourLabel, 'gridLayout');
						}
					}

				}
				// Normal CEs
				else {
					// Is default property!?
					if (array_key_exists($contentElementKey, $this->defaultProperties)) {
						$this->createCheckbox($contentElementKey, $label, 'default');
					}
					// Is ctype specific!
					else {
						$this->createCheckbox($contentElementKey, $label, 'ctype');
					}
				}

			}
		}
		// Merge checkbox groups
		$checkboxes = '';
		$checkboxes .= $this->getMergedCheckboxes('default');
		$checkboxes .= $this->getMergedCheckboxes('ctype', $cType);
		$checkboxes .= $this->getMergedCheckboxes('gridLayout', $gridLayout);
		if ($checkboxes === '') {
			$checkboxes = $this->getLanguageService()->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:behaviour.no_behaviour_available');
		}
		// Process current classes/identifiers
		$setClasses = array_intersect($values, $this->valuesAvailable);
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
		$missedField = $this->getMissedFields($values, $this->valuesAvailable);
		return '<div class="contentBehaviour">' . $checkboxes . $hiddenField . $missedField . '</div>';
	}

	/**
	 * Creates a checkbox
	 *
	 * @param $key \string Key/name of the checkbox
	 * @param $label \string Label of the checkbox
	 * @param $type \string Type of the checkbox property
	 */
	protected function createCheckbox($key, $label, $type) {
		$label = $this->getLanguageService()->sL($label);
		$this->valuesAvailable[] = $key;
		$checked = (isset($this->valuesFlipped[$key])) ? 'checked="checked"' : '';
		$checkbox = '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">' . LF;
		$checkbox .= '<label class="themes-checkbox-label" title="' . $label . '">' . LF;
		$checkbox .= '<input type="checkbox" name="' . $key . '" ' . $checked . '>' . LF;
		$checkbox .= $label . '</label>' . LF;
		$checkbox .= '</div>' . LF;
		$this->checkboxesArray[$type][] = $checkbox;
	}

	/**
	 * Merge checkboxes into a group
	 *
	 * @param $type \string Type of the checkbox property
	 * @param $label \string Label of the checkbox
	 * @return string Grouped checkboxes
	 */
	protected function getMergedCheckboxes($type, $labelInfo='') {
		$checkboxes = '';
		if (!empty($this->checkboxesArray[$type])) {
			$labelKey = 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:behaviour.' . strtolower($type) . '_group_label';
			$label = $this->getLanguageService()->sL($labelKey);
			if(trim($labelInfo)!='') {
				$label .= '(' . $labelInfo . ')';
			}
			$checkboxes .= '<label class="t3js-formengine-label themes-label-' . $type . ' col-xs-12">' . $label . ':</label>';
			$checkboxes .= implode('', $this->checkboxesArray[$type]). LF;
		}
		return $checkboxes;
	}
}