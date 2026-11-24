<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractThemesTestCase extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['install', 'form'];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/gridelements',
        'typo3conf/ext/themes',
        __DIR__ . '/Fixtures/Extensions/themes_fixture_txt',
        __DIR__ . '/Fixtures/Extensions/themes_fixture_typoscript',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            // every extension with Meta/theme.yaml is a theme, not only theme_*
            'themes' => ['themeExtensionsStartWith' => ''],
        ],
    ];
}
