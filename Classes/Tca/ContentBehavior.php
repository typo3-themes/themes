<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Behavior row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentBehavior {

	/**
	 * Render a Content Behavior row
	 *
	 * @param array $parameters
	 * @param mixed $parentObject
	 * @return string
	 */
	public function renderField(array &$parameters, &$parentObject) {

		// Vars
		$uid   = &$parameters["row"]["uid"];
		$pid   = $parameters["row"]["pid"];
		$name  = $parameters['itemFormElName'];
		$value = $parameters['itemFormElValue'];
		$values = array_flip(explode(',', $value));

		// Type: default or ctype specific
		$type = 'default';
		
		// Get configuration
		$behaviors = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.behavior.' . $type,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		
		// Build checkboxes
		$checkboxes = '';
		if(isset($behaviors['properties']) && is_array($behaviors['properties'])) {
			foreach($behaviors['properties'] as $key=>$label) {
				$checked = (isset($values[$key])) ? 'checked="checked"' : '';
				$checkboxes.= '<div style="width:200px;float:left">' . LF;
				$checkboxes.= '<input type="checkbox" onchange="contentBehaviorChange(this)" name="' . $key . '" id="theme-behavior-' . $key . '" ' . $checked . '>' . LF;
				$checkboxes.= '<label for="theme-behavior-' . $key . '">' . $label . '</label>' . LF;
				$checkboxes.= '</div>' . LF;
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
		$script = '<script type="text/javascript">'.LF;
		$script.= 'function contentBehaviorChange(field) {'.LF;
		$script.= '  if(field.checked) {'.LF;
		$script.= '    jQuery("#contentBehavior").addClass(field.name);'.LF;
		$script.= '  }'.LF;
		$script.= '  else {'.LF;
		$script.= '    jQuery("#contentBehavior").removeClass(field.name);'.LF;
		$script.= '  }'.LF;
		$script.= '  jQuery("#contentBehavior").attr("value", jQuery("#contentBehavior").attr("class").replace(/\ /g, ","));'.LF;
		$script.= '}'.LF;
		$script.= '</script>'.LF;
		
		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" id="contentBehavior" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="' . htmlspecialchars(str_replace(',', ' ', $value)) . '">' . LF;
		
		return '<div>' . $checkboxes . $hiddenField . $script . '</div>';
	}


}

?>