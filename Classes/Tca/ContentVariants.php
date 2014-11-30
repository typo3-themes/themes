<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Variant row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentVariants extends AbstractContentRow {

	protected $checkboxesArray = array();
	protected $valuesFlipped = array();
	protected $valuesAvailable = array();

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
		$gridLayout = $parameters['row']['tx_gridelements_backend_layout'];

		// Get values
		$values = explode(',', $value);
		$this->valuesFlipped = array_flip($values);
		$this->valuesAvailable = array();

		// Get configuration
		$variants = $this->getMergedConfiguration($pid, 'variants', $cType);

		// Build checkboxes
		$this->checkboxesArray['default'] = array();
		$this->checkboxesArray['ctype'] = array();
		$this->checkboxesArray['gridLayout'] = array();
		if (isset($variants['properties']) && is_array($variants['properties'])) {
			foreach ($variants['properties'] as $contentElementKey => $label) {

				// GridElements: are able to provide grid-specific variants
				if (is_array($label) && $cType === 'gridelements_pi1') {
					$contentElementKey = substr($contentElementKey, 0, -1);

					// Variant for all GridElements
					if ($contentElementKey == 'default' && !empty($label)) {
						foreach ($label as $gridLayoutKey => $gridLayoutVariantLabel) {
							$this->createCheckbox($gridLayoutKey, $gridLayoutVariantLabel, 'ctype');
						}
					}
					// Variant only for selected GridElement
					else if ($contentElementKey == $gridLayout && !empty($label)) {
						foreach ($label as $gridLayoutKey => $gridLayoutVariantLabel) {
							$this->createCheckbox($gridLayoutKey, $gridLayoutVariantLabel, 'gridLayout');
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
		$checkboxes .= $this->getMergedCheckboxes('ctype');
		$checkboxes .= $this->getMergedCheckboxes('gridLayout');
		if ($checkboxes === '') {
			$checkboxes = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.no_variants_available');
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
		$script .= 'function contentVariantChange(field) {' . LF;
		$script .= '  if (field.checked) {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentVariant input[readonly=\'readonly\']").addClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  else {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentVariant input[readonly=\'readonly\']").removeClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentVariant input[readonly=\'readonly\']").attr("value", ' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentVariant input[readonly=\'readonly\']").attr("class").replace(/\ /g, ","));' . LF;
		$script .= '}' . LF;
		$script .= '</script>' . LF;

		$setClasses = array_intersect($values, $this->valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));

		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" name="' . htmlspecialchars($name) . '" value="' . $setValue . '" class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $this->valuesAvailable);

		return '<div class="contentVariant">' . $checkboxes . $hiddenField . $script . $missedField . '</div>';
	}

	/**
	 * Creates a checkbox
	 *
	 * @param $key \string Key/name of the checkbox
	 * @param $label \string Label of the checkbox
	 * @param $type \string Type of the checkbox property
	 */
	protected function createCheckbox($key, $label, $type) {
		$this->valuesAvailable[] = $key;
		$checked = (isset($this->valuesFlipped[$key])) ? 'checked="checked"' : '';
		$checkbox = '<div style="width:200px;float:left">' . LF;
		$checkbox .= '<label><input type="checkbox" onchange="contentVariantChange(this)" name="' . $key . '" ' . $checked . '>' . LF;
		$checkbox .= $GLOBALS['LANG']->sL($label) . '</label>' . LF;
		$checkbox .= '</div>' . LF;
		$this->checkboxesArray[$type][] = $checkbox;
	}

	/**
	 * Merge checkboxes into a group
	 *
	 * @param $type \string Type of the checkbox property
	 * @return string Grouped checkboxes
	 */
	protected function getMergedCheckboxes($type) {
		$checkboxes = '';
		if (!empty($this->checkboxesArray[$type])) {
			$labelKey = 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.' . strtolower($type) . '_group_label';
			$label = $GLOBALS['LANG']->sL($labelKey);
			$checkboxes .= '<fieldset style="border:0 solid">' . LF;
			$checkboxes .= '<legend style="font-weight:bold">' . $label . ':</legend>' . implode('', $this->checkboxesArray[$type]). LF;
			$checkboxes .= '</fieldset>' . LF;
		}
		return $checkboxes;
	}
	
}