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
				if (isset($settings['columnSettings.']) && is_array($settings['columnSettings.'])) {
					foreach ($settings['columnSettings.'] as $visibilityKey => $visibilityLabel) {
						$start = $visibilityKey === 'width' ? 1 : 0;
						$tempKey = 'responsive-' . $groupKey . '-' . $visibilityKey;

						// Collect selectable values
						for ($i = $start; $i <= 12; $i++) {
							$valuesAvailable[] = $tempKey . '-' . $i;
						}

						// build radiobox
						$radiobuttons .= '<div style="float:left">' . LF;
						//$radiobuttons .= '<input type="radio" name="' . $groupKey . '" value="' . $tempKey . '" id="theme-enforceequalcolumnheight-' . $tempKey . '" ' . $selected .  '>' . LF;
						$radiobuttons .= '<label style="width:50px;display:inline-block">' . $GLOBALS['LANG']->sL($visibilityLabel) . '</label>' . LF;

						$radiobuttons .= '<select style="width:110px" onchange="contentColumnSettingsChange(this)" name="' . $tempKey . '">' . LF;
						$radiobuttons .= '<option value="">default</option>' . LF;
						for ($i = $start; $i <= 12; $i++) {

							// set the selected value
							$selected = (isset($valuesFlipped[$tempKey . '-' . $i])) ? 'selected="selected"' : '';

							$radiobuttons .= '<option value="' . $tempKey . '-' . $i . '" ' . $selected .  '>' . $visibilityKey . '-' . $i . '</option>' . LF;
						}
						$radiobuttons .= '</select>' . LF;
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
		$script .= 'function contentColumnSettingsChange(field) {' . LF;
		//$script .= 'console.log("in:", field);' . LF;
		$script .= '  jQuery.each(jQuery(".contentColumnSettings select[name=\'"+field.name+"\'] option"), function(index, node) {' . LF;
		//$script .= '    console.log("remove:", node);' . LF;
		//$script .= '    console.log("remove:", node.value);' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentColumnSettings input[readonly=\'readonly\']").removeClass(node.value);' . LF;
		$script .= '  });' . LF;
		//$script .= '  console.log("add:", field.value);' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentColumnSettings input[readonly=\'readonly\']").addClass(field.value);' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentColumnSettings input[readonly=\'readonly\']").attr("value", ' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentColumnSettings input[readonly=\'readonly\']").attr("class").replace(/\ /g, ","));' . LF;
		$script .= '}' . LF;
		$script .= '</script>' . LF;

		$setClasses = array_intersect($values, $valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));

		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" name="' . htmlspecialchars($name) . '" value="' . $setValue . '"  class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);

		return '<div class="contentColumnSettings">' . $radiobuttons . $hiddenField . $script . $missedField . '</div>';
	}
}