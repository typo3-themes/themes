<?php

namespace KayStrobach\Themes\Frontend;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class CssClassMapper.
 */
class CssClassMapper
{

    /**
     * Maps generic class names of a record to the official class names of the underlying framework.
     *
     * @param string $content
     * @param array  $conf
     *
     * @return string
     */
    public function mapGenericToFramework($content = '', $conf = [])
    {
        if ($content) {
            $hashKey = md5($content.serialize($conf));
            if (!isset($GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey])) {
                $frameworkClasses = [];
                $genericClasses = array_flip(explode(',', $content));
                foreach ($conf as $checkConfKey => $checkConfValue) {
                    if (!is_array($conf[$checkConfValue]) && $checkConfValue && strpos($checkConfValue, '<') === 0) {
                        $checkConfArray = explode('.', ltrim($checkConfValue, '< '));
                        $conf[$checkConfKey] = $GLOBALS['TSFE']->tmpl->setup[array_shift($checkConfArray).'.'];
                        foreach ($checkConfArray as $checkConfArrayKey) {
                            $conf[$checkConfKey] = $conf[$checkConfKey][$checkConfArrayKey.'.'];
                        }
                    }
                    if (is_array($conf[$checkConfKey])) {
                        $frameworkClasses = array_merge($frameworkClasses, $conf[$checkConfKey]);
                    }
                }
                $mappedClasses = array_intersect_key($frameworkClasses, $genericClasses);
                $GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey] = implode(' ', $mappedClasses);
            }
            return $GLOBALS['TSFE']->themesCssClassMapperCache[$hashKey];
        } else {
            return '';
        }
    }

}
