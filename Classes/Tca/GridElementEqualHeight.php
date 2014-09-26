<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a grid element equal height
 *
 * @package KayStrobach\Themes\Tca
 */
class GridElementEqualHeight {

	/**
	 * Render a grid element equal height
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
		$values = explode(',', $value);
		$valuesFlipped = array_flip($values);
		$valuesAvailable = array();

		// Type: default or ctype specific
		$type = 'default';
		
		// Get configuration
		$responsives = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.content.responsive.' . $type,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		
		// Build checkboxes
		$radiobuttons = '';
		if(isset($responsives['properties']) && is_array($responsives['properties'])) {
			foreach($responsives['properties'] as $groupKey=>$settings) {

				// Validate groupKey and get label
				$groupKey = substr($groupKey, 0, -1);
				$label = isset($settings['label']) ? $settings['label'] : $groupKey;
				
				$radiobuttons.= '<fieldset id="themeResponsiveValues" style="border:0 solid;border-right: 1px solid #ccc;width:120px;float:left;">' . LF;
				$radiobuttons.= '<legend style="font-weight:bold">' . $label . '</legend>' . LF;
				if(isset($settings['visibility.']) && is_array($settings['visibility.'])) {

					// check if theres already a value selected
					$valueSetted = FALSE;
					foreach($settings['visibility.'] as $visibilityKey=>$visibilityLabel) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						if(!$valueSetted) {
							$valueSetted = isset($valuesFlipped[$tempKey]);
						}
					}
					
					foreach($settings['visibility.'] as $visibilityKey=>$visibilityLabel) {
						$tempKey = $groupKey . '-' . $visibilityKey;
						$valuesAvailable[] = $tempKey;
						
						// set the selected value
						if($valueSetted) {
							$selected = (isset($valuesFlipped[$tempKey])) ? 'checked="checked"' : '';
						}
						// set the default value, this means the first one
						else {
							$selected = 'checked="checked"';
							$valueSetted = TRUE;
						}
						
						// build radiobox
						$radiobuttons.= '<div style="float:left">' . LF;
						$radiobuttons.= '<input type="radio" onchange="contentResponsiveChange(this)" name="' . $groupKey . '" value="' . $tempKey . '" id="theme-responsive-' . $tempKey . '" ' . $selected . '>' . LF;
						$radiobuttons.= '<label for="theme-responsive-' . $tempKey . '">' . $visibilityLabel . '</label>' . LF;
						$radiobuttons.= '</div>' . LF;
					}
				}
				$radiobuttons.= '</fieldset>' . LF;
				
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
		$script.= 'function contentResponsiveChange(field) {'.LF;
		$script.= 'console.log("in:", field);'.LF;
		$script.= '  jQuery.each(jQuery("#themeResponsiveValues input[name=\'"+field.name+"\']"), function(index, node) {'.LF;
		//$script.= '    console.log("remove:", node);'.LF;
		//$script.= '    console.log("remove:", node.value);'.LF;
		$script.= '    jQuery("#contentResponsive").removeClass(node.value);'.LF;
		$script.= '  });'.LF;
		//$script.= '  console.log("add:", field.value);'.LF;
		$script.= '  jQuery("#contentResponsive").addClass(field.value);'.LF;
		$script.= '  jQuery("#contentResponsive").attr("value", jQuery("#contentResponsive").attr("class").replace(/\ /g, ","));'.LF;
		$script.= '}'.LF;
		$script.= '</script>'.LF;

		$settedClasses = array_intersect($values, $valuesAvailable);
		$settedClass = htmlspecialchars(implode(' ', $settedClasses));
		$settedValue = htmlspecialchars(implode(',', $settedClasses));
		
		$hiddenField = '<input style="width:90%;background-color:#dadada" readonly="readonly" type="text" id="contentResponsive" name="' . htmlspecialchars($name) . '" value="' . $settedValue . '"  class="' . $settedClass . '">' . LF;

		// Missed classes
		$missedField = '';
		$missedClasses = array_diff($values, $valuesAvailable);
		if(!empty($missedClasses)) {
			$missedClass = htmlspecialchars(implode(',', $missedClasses));
			$missedField = '<span style="display:inline-block;color: #C00">Unavailable classes: '. $missedClass . '</span>';
		}
		
		return '<div>' . $radiobuttons . $hiddenField . $script . $missedField . '</div>';
	}


}

?>