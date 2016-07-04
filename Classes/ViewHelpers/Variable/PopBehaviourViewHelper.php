<?php
namespace KayStrobach\Themes\ViewHelpers\Variable;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * @author Thomas Deuling <typo3@coding.ms>, coding.ms
 * @package Themes
 * @subpackage ViewHelpers\Variable
 */
class PopBehaviourViewHelper extends AbstractViewHelper {

	/**
	 * Pop the behaviour with the variable in $name.
	 *
	 * @param string $name
	 * @return void
	 */
	public function render($name) {
		if (FALSE === $this->templateVariableContainer->exists('themes')) {
			return NULL;
		}
		else {
			$themes = $this->templateVariableContainer->get('themes');
			if(isset($themes['behaviour'])) {
				$value = '';
				if(isset($themes['behaviour']['css'])) {
					if(isset($themes['behaviour']['css'][$name])) {
						$value = $themes['behaviour']['css'][$name];
						unset($themes['behaviour']['css'][$name]);
					}
				}
				if(isset($themes['behaviour']['css2key'])) {
					if(isset($themes['behaviour']['css2key'][$value])) {
						unset($themes['behaviour']['css2key'][$value]);
					}
				}
				$themes['behaviour']['key2css'] = $themes['behaviour']['css'];
				$themes['behaviour']['cssClasses'] = implode(' ', $themes['behaviour']['css']);
				// Write back
				$this->templateVariableContainer->remove('themes');
				$this->templateVariableContainer->add('themes', $themes);
			}
		}
		return NULL;
	}

}
