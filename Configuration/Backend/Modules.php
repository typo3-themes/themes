<?php

return [
    'themes' => [
        'parent' => 'web',
        'access' => 'user',
        'iconIdentifier' => 'module-themes',
        'path' => '/module/themes',
        'labels' => 'LLL:EXT:themes/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'Themes',
        'controllerActions' => [
            \KayStrobach\Themes\Controller\EditorController::class => [
                'index',
                'update',
                'showTheme',
                'setTheme',
                'showThemeDetails',
                'saveCategoriesFilterSettings',
            ],
        ],
    ],
];
