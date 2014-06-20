<?php

namespace KayStrobach\Themes\ViewHelpers;

/**
 * Access constants
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
class ConstantViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Gets a constant
	 *
	 * @param string $constant The name of the constant
	 * @return string Constant-Value
	 *
	 * = Examples =
	 *
	 * <code title="Example">
	 * <theme:constant constant="themes.configuration.baseurl" />
	 * </code>
	 * <output>
	 * http://yourdomain.tld/
	 * (depending on your domain)
	 * </output>
	 */
	public function render($constant = '') {
		return isset($GLOBALS['TSFE']->tmpl->flatSetup[$constant]) ? $GLOBALS['TSFE']->tmpl->flatSetup[$constant] : NULL;
	}

}
