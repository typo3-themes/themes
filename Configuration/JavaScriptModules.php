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
    ],
];
