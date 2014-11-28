<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Variant row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentVariants extends AbstractContentRow {

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
		$valuesFlipped = array_flip($values);
		$valuesAvailable = array();

		// Get configuration
		$variants = $this->getMergedConfiguration($pid, 'variants', $cType);

		// Build checkboxes
		$checkboxesArray = array();
		$checkboxesArray['default'] = array();
		$checkboxesArray['ctype'] = array();
		if (isset($variants['properties']) && is_array($variants['properties'])) {
			foreach ($variants['properties'] as $key => $label) {
				$fullKey = 'variants-' . $key;
				$valuesAvailable[] = $fullKey;
				$checked = (isset($valuesFlipped[$key])) ? 'checked="checked"' : '';
				$checkbox = '<div style="width:200px;float:left">' . LF;
				$checkbox .= '<label><input type="checkbox" onchange="contentVariantChange(this)" name="' . $fullKey . '" ' . $checked . '>' . LF;
				$checkbox .= $GLOBALS['LANG']->sL($label) . '</label>' . LF;
				$checkbox .= '</div>' . LF;
				if(array_key_exists($key, $this->defaultProperties)) {
					$checkboxesArray['default'][] = $checkbox;
				}
				else {
					$checkboxesArray['ctype'][] = $checkbox;
				}
			}
		}

		$checkboxes = '';
		if(!empty($checkboxesArray['default'])) {
			$label = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.default_label');
			$checkboxes = '<fieldset style="border:0 solid">' . LF;
			$checkboxes .= '<legend style="font-weight:bold">' . $label . ':</legend>' . implode('', $checkboxesArray['default']). LF;
			$checkboxes .= '</fieldset>' . LF;
		}
		if(!empty($checkboxesArray['ctype'])) {
			$label = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.ctype_label');
			$checkboxes .= '<fieldset style="border:0 solid">' . LF;
			$checkboxes .= '<legend style="font-weight:bold">' . $label . ':</legend>' . implode('', $checkboxesArray['ctype']). LF;
			$checkboxes .= '</fieldset>' . LF;
		}
		if($checkboxes==='') {
			$checkboxes = $GLOBALS['LANG']->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:variants.no_variants_available');;
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

		$setClasses = array_intersect($values, $valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));

		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" name="' . htmlspecialchars($name) . '" value="' . $setValue . '" class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);

		return '<div class="contentVariant">' . $checkboxes . $hiddenField . $script . $missedField . '</div>';
	}
}