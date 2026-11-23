<?php

return [
    'dependencies' => [
        'backend',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@themes/themes-backend-tca.js' => 'EXT:themes/Resources/Public/JavaScript/ThemesBackendTca.js',
        '@themes/colorpicker.js' => 'EXT:themes/Resources/Public/JavaScript/Colorpicker.js',
        '@themes/themes-backend-module.js' => 'EXT:themes/Resources/Public/JavaScript/ThemesBackendModule.js',
    ],
];
