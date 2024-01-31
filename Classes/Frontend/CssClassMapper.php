<?php

declare(strict_types=1);

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
     *
     * @return string
     */
    public function mapGenericToFramework(string $content = '', array $conf = []): string
    {
        if ($content) {
            $frameworkClasses = [];
            $genericClasses = array_flip(explode(',', $content));
            foreach ($conf as $checkConfKey => $checkConfValue) {
                if (empty($conf[$checkConfValue]) && $checkConfValue && str_starts_with((string) $checkConfValue, '<')) {
                    $checkConfArray = explode('.', ltrim((string) $checkConfValue, '< '));
                    $conf[$checkConfKey] = $GLOBALS['TSFE']->tmpl->setup[array_shift($checkConfArray) . '.'];
                    foreach ($checkConfArray as $checkConfArrayKey) {
                        $conf[$checkConfKey] = $conf[$checkConfKey][$checkConfArrayKey . '.'];
                    }
                }
                if (!empty($conf[$checkConfKey])) {
                    $frameworkClasses = array_merge($frameworkClasses, $conf[$checkConfKey]);
                }
            }
            $mappedClasses = array_intersect_key($frameworkClasses, $genericClasses);
            return implode(' ', $mappedClasses);
        }
        return '';
    }
}
