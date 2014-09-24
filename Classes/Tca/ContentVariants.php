<?php

namespace KayStrobach\Themes\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render a Content Variant row
 *
 * @package KayStrobach\Themes\Tca
 */
class ContentVariants {

	/**
	 * Render a Content Variant row
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
		$variants = $GLOBALS["BE_USER"]->getTSConfig(
			'themes.variants.' . $type,
			\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid)
		);
		
		// Build checkboxes
		$checkboxes = '';
		if(isset($variants['properties']) && is_array($variants['properties'])) {
			foreach($variants['properties'] as $key=>$label) {
				$checked = (isset($values[$key])) ? 'checked="checked"' : '';
				$checkboxes.= '<div style="width:200px;float:left">' . LF;
				$checkboxes.= '<input type="checkbox" onchange="contentVariantChange(this)" name="' . $key . '" id="theme-variant-' . $key . '" ' . $checked . '>' . LF;
				$checkboxes.= '<label for="theme-variant-' . $key . '">' . $label . '</label>' . LF;
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
		$script.= 'function contentVariantChange(field) {'.LF;
		$script.= '  if(field.checked) {'.LF;
		$script.= '    jQuery("#contentVariant").addClass(field.name);'.LF;
		$script.= '  }'.LF;
		$script.= '  else {'.LF;
		$script.= '    jQuery("#contentVariant").removeClass(field.name);'.LF;
		$script.= '  }'.LF;
		$script.= '  jQuery("#contentVariant").attr("value", jQuery("#contentVariant").attr("class").replace(/\ /g, ","));'.LF;
		$script.= '}'.LF;
		$script.= '</script>'.LF;
		
		$hiddenField = '<input type="text" id="contentVariant" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="' . htmlspecialchars(str_replace(',', ' ', $value)) . '">' . LF;
		
		return '<div>' . $checkboxes . $hiddenField . $script . '</div>';
	}


}

?>