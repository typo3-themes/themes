<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * Render a row for enforcing equal height of a column
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentEnforceEqualColumnHeight extends AbstractContentRow {

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
		$gridLayout = $parameters['row']['tx_gridelements_backend_layout'];

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

				$radiobuttons .= '<fieldset style="border:0 solid;border-right: 1px solid #ccc;width:120px;float:left;">' . LF;
				$radiobuttons .= '<legend style="font-weight:bold">' . $GLOBALS['LANG']->sL($label) . '</legend>' . LF;
				if (isset($settings['rowSettings.']) && is_array($settings['rowSettings.'])) {

					// check if theres already a value selected
					$valueSet = FALSE;
					foreach ($settings['rowSettings.'] as $visibilityKey => $_) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						if (!$valueSet) {
							$valueSet = isset($valuesFlipped[$tempKey]);
						}
					}

					foreach ($settings['rowSettings.'] as $visibilityKey => $visibilityLabel) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						$valuesAvailable[] = $tempKey;

						$checked = (isset($valuesFlipped[$tempKey])) ? 'checked="checked"' : '';

						// build checkbox
						$radiobuttons .= '<div style="float:left">' . LF;
						$radiobuttons .= '<label><input type="checkbox" onchange="contentEnforceEqualColumnHeightChange(this)" name="' . $tempKey . '" value="' . $tempKey . '" ' . $checked . '>' . LF;
						$radiobuttons .= $GLOBALS['LANG']->sL($visibilityLabel) . '</label>' . LF;
						$radiobuttons .= '</div>' . LF;
					}
				}
				$radiobuttons .= '</fieldset>' . LF;
			}
		}

		/**
		 * Include jQuery in backend
		 * @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
		 */
		$pageRenderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Page\\PageRenderer');
		$pageRenderer->loadJquery(NULL, NULL, $pageRenderer::JQUERY_NAMESPACE_DEFAULT_NOCONFLICT);

		/**
		 * @todo auslagern!!
		 */
		$script = '<script type="text/javascript">' . LF;
		$script .= 'function contentEnforceEqualColumnHeightChange(field) {' . LF;
		$script .= '  if (field.checked) {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").addClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  else {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").removeClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("value", ' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("class").replace(/\ /g, ","));' . LF;
		$script .= '}' . LF;
		$script .= '</script>' . LF;

		$setClasses = array_intersect($values, $valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));

		$inputType = 'hidden';
		if($this->isAdmin()) {
			$inputType = 'text';
		}
		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="' . $inputType . '" name="' . htmlspecialchars($name) . '" value="' . $setValue . '"  class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);

		return '<div class="contentEnforceEqualColumnHeight">' . $radiobuttons . $hiddenField . $script . $missedField . '</div>';
	}

}