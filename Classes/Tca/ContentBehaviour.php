<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Behaviour row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentBehaviour extends AbstractContentRow {

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

		// Get values
		$values = explode(',', $value);
		$valuesFlipped = array_flip($values);
		$valuesAvailable = array();

		// Get configuration
		$behaviours = $this->getMergedConfiguration($pid, 'behaviour', $cType);

		// Build checkboxes
		$checkboxes = '';
		if (isset($behaviours['properties']) && is_array($behaviours['properties'])) {
			foreach ($behaviours['properties'] as $key => $label) {
				$key = 'behaviour-' . $key;
				$valuesAvailable[] = $key;
				$checked = (isset($valuesFlipped[$key])) ? 'checked="checked"' : '';
				$checkboxes .= '<div style="width:200px;float:left">' . LF;
				$checkboxes .= '<label><input type="checkbox" onchange="contentBehaviourChange(this)" name="' . $key . '" ' . $checked . '>' . LF;
				$checkboxes .= $GLOBALS['LANG']->sL($label) . '</label>' . LF;
				$checkboxes .= '</div>' . LF;
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
		$script .= 'function contentBehaviourChange(field) {' . LF;
		$script .= '  if (field.checked) {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentBehaviour input[readonly=\'readonly\']").addClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  else {' . LF;
		$script .= '    jQuery(field).closest(".t3-form-field-item").find(".contentBehaviour input[readonly=\'readonly\']").removeClass(field.name);' . LF;
		$script .= '  }' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentBehaviour input[readonly=\'readonly\']").attr("value", ' . LF;
		$script .= '  jQuery(field).closest(".t3-form-field-item").find(".contentBehaviour input[readonly=\'readonly\']").attr("class").replace(/\ /g, ","));' . LF;
		$script .= '}' . LF;
		$script .= '</script>' . LF;

		$setClasses = array_intersect($values, $valuesAvailable);
		$setClass = htmlspecialchars(implode(' ', $setClasses));
		$setValue = htmlspecialchars(implode(',', $setClasses));

		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" name="' . htmlspecialchars($name) . '" value="' . $setValue . '" class="' . $setClass . '">' . LF;

		// Missed classes
		$missedField = $this->getMissedFields($values, $valuesAvailable);

		return '<div class="contentBehaviour">' . $checkboxes . $hiddenField . $script . $missedField . '</div>';
	}

}