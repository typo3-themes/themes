<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\EventListener\AfterTemplatesHaveBeenDeterminedEventListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;

class ThemeTest extends AbstractThemesTestCase
{
    public function testThemesWithTxtFilesAreLoaded(): void
    {
        // e.g. theme_bootstrap and its child themes
        $theme = new Theme('themes_fixture_txt');
        self::assertStringEndsWith('Configuration/TypoScript/setup.txt', $theme->getTypoScriptAbsPath());
        self::assertStringEndsWith('Configuration/TypoScript/constants.txt', $theme->getTypoScriptConstantsAbsPath());
        // title from ext_emconf.php, which has no description (was a TypeError): from composer.json
        self::assertSame('themes test fixture (txt)', $theme->getTitle());
        self::assertSame('Test fixture of themes', $theme->getDescription());
    }

    public function testTypoScriptFilesArePreferred(): void
    {
        $theme = new Theme('themes_fixture_typoscript');
        self::assertStringEndsWith('Configuration/TypoScript/setup.typoscript', $theme->getTypoScriptAbsPath());
    }

    public function testBrokenMetaDataDoesNotBreakTheTheme(): void
    {
        self::assertSame('themes test fixture (typoscript)', (new Theme('themes_fixture_typoscript'))->getTitle());
    }

    public function testTypoScriptOfTheSelectedThemeIsAddedToTheTemplate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/constants_editor.csv');
        $row = ['uid' => 1, 'pid' => 1, 'root' => 1, 'clear' => 3, 'constants' => 'own.constant = 1', 'config' => 'page = PAGE', 'tx_themes_skin' => 'themes_fixture_txt'];
        $event = new AfterTemplatesHaveBeenDeterminedEvent([], null, [$row]);
        $this->get(AfterTemplatesHaveBeenDeterminedEventListener::class)($event);
        $templateRow = $event->getTemplateRows()[0];

        self::assertStringContainsString('fixture.setup = txt', $templateRow['config']);
        self::assertStringEndsWith('page = PAGE', $templateRow['config'], 'the template comes after the theme');
        self::assertStringContainsString('fixture.constants = txt', $templateRow['constants']);
        self::assertStringEndsWith('own.constant = 1', $templateRow['constants']);
    }

    public function testLanguageConstantsAreGeneratedForEveryLanguageOfTheSite(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/constants_editor.csv');
        $site = new Site('school', 1, [
            'base' => 'https://example.com/school/',
            'languages' => [
                ['languageId' => 0, 'title' => 'Deutsch', 'navigationTitle' => 'Deutsch', 'locale' => 'de_DE.UTF-8', 'flag' => 'de', 'base' => '/'],
                ['languageId' => 1, 'title' => 'English', 'navigationTitle' => 'English', 'locale' => 'en_GB.UTF-8', 'flag' => 'gb', 'base' => '/en/'],
            ],
        ]);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/school/'))->withAttribute('site', $site);
        try {
            $theme = new Theme('themes_fixture_txt');
            $theme->buildTypoScript(['uid' => 1, 'pid' => 1]);
            $constants = $theme->getConstants();
        } finally {
            unset($GLOBALS['TYPO3_REQUEST']);
        }
        // the prefix was overwritten by the loop variable: "0.current {", "1.available ="
        self::assertStringContainsString("themes.languages.available = 0,1\n", $constants);
        self::assertStringContainsString("[siteLanguage(\"languageId\") == 1]\nthemes.languages.current {\n uid = 1\n label = English\n", $constants);
        self::assertStringContainsString(" isoCode = en_GB.UTF-8\n isoCodeShort = en\n isoCodeHtml = en-GB\n", $constants);
        self::assertStringNotContainsString('0.current', $constants);
    }

    public function testPreviewImagesAreUsableInTheBackend(): void
    {
        // Meta/Screenshots is not public in composer mode: data URI
        self::assertStringStartsWith('data:image/png;base64,', (new Theme('themes_fixture_txt'))->getPreviewImage());
        // without screenshot: the default image of themes, below Resources/Public
        $default = (new Theme('themes_fixture_typoscript'))->getPreviewImage();
        self::assertStringEndsWith('Images/screenshot.gif', $default);
        self::assertStringNotContainsString('typo3conf/ext/../', $default);
        self::assertStringNotContainsString('EXT:', $default);
    }
}
