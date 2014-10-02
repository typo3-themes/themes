<?php

namespace KayStrobach\Themes\Frontend;

/**
 * Class CssClassMapper
 *
 * @package KayStrobach\Themes\Frontend
 */
class CssClassMapper {

	/**
	 * Maps generic class names of a record to the official class names of the underlying framework
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function mapGenericToFramework($content = '', $conf = array()) {
		if($content) {
			$hashKey = md5($content . serialize($conf));
			if(!isset($GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey])) {
				$frameworkClasses = array();
				$genericClasses = array_flip(explode(',', $content));
				foreach($conf as $checkConfKey => $checkConfValue) {
					if(!is_array($conf[$checkConfValue]) && $checkConfValue && strpos($checkConfValue, '<') === 0) {
						$checkConfValue = ltrim($checkConfValue, '< lib.') . '.';
						$conf[$checkConfKey] = $GLOBALS['TSFE']->tmpl->setup['lib.'][$checkConfValue];
					}
					if(is_array($conf[$checkConfKey])) {
						$frameworkClasses = array_merge($frameworkClasses, $conf[$checkConfKey]);
					}
				}
				$mappedClasses = array_intersect_key($frameworkClasses, $genericClasses);
				$GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey] = implode(' ', $mappedClasses);
			}
			return $GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey];
		}
	}

}