<?php

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

$EM_CONF['themes'] = [
    'title' => 'THEMES - The theme engine',
    'description' => '',
    'category' => 'fe',
    'version' => '10.0.0',
    'state' => 'stable',
    'author' => 'Themes-Team (Kay Strobach, Jo Hasenau, Thomas Deuling)',
    'author_email' => 'team@typo3-themes.org',
    'author_company' => 'private',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.7-11.5.99',
            'gridelements' => '9.2.1-11.0.99',
        ],
        'conflicts' => [
            'belayout_tsprovider' => '0.0.0-1.99.99',
            'yaml_parser' => '0.0.0-1.99.99',
        ],
        'suggests' => [
        ],
    ],
    '_md5_values_when_last_written' => '',
];
