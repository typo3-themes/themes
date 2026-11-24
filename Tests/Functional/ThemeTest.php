<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\EventListener\AfterTemplatesHaveBeenDeterminedEventListener;
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
}
