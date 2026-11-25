<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\Migration\IncludeTypoScriptConverter;
use PHPUnit\Framework\TestCase;

/**
 * Works on files only, no TYPO3 instance needed.
 */
final class IncludeTypoScriptConverterTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir() . '/themes-convert-' . bin2hex(random_bytes(4)) . '/';
        $this->write('theme_base/Configuration/TypoScript/setup.txt', "page = PAGE\n");
        $this->write('theme_base/Configuration/TypoScript/constants.txt', "a = 1\n");
        $this->write('theme_base/Configuration/PageTS/tsconfig.txt', "mod.x = 1\n");
        $this->write('theme_child/Configuration/TypoScript/setup.typoscript', implode("\n", [
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme_base/Configuration/TypoScript/setup.txt">',
            '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/TypoScript/Library/" extensions="setupts">',
            '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/Elements/" extensions="typoscript">',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/setup.txt" condition="[extension.isLoaded(\'foreign\') == 1]">',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/legacy.txt">',
            '[page["uid"] == 1]',
            "\t<INCLUDE_TYPOSCRIPT: source=\"FILE:./Library/b.setupts\">",
            '[END]',
            '#<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme_base/Configuration/TypoScript/constants.txt">',
            '',
        ]));
        $this->write('theme_child/Configuration/TypoScript/constants.typoscript', '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/TypoScript/Library/" extensions="constantsts">' . "\n");
        $this->write('theme_child/Configuration/TypoScript/Library/b.setupts', "b = 1\n");
        $this->write('theme_child/Configuration/TypoScript/Library/a.setupts', "a = 1\n");
        $this->write('theme_child/Configuration/TypoScript/Library/a.constantsts', "a = 1\n");
        $this->write('theme_child/Configuration/TypoScript/Library/Sub/c.setupts', "c = 1\n");
        // a .setupts next to the included .typoscript files: a wildcard would load it as well after the rename
        $this->write('theme_child/Configuration/Elements/x.typoscript', "x = 1\n");
        $this->write('theme_child/Configuration/Elements/y.typoscript', "y = 1\n");
        $this->write('theme_child/Configuration/PageTS/tsconfig.txt', implode("\n", [
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme_base/Configuration/PageTS/tsconfig.txt">',
            '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/PageTS/Library/" extensions="pagets">',
            '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/PageTS/Empty/" extensions="pagets">',
            '',
        ]));
        $this->write('theme_child/Configuration/PageTS/Library/mod.pagets', "mod.y = 1\n");
        mkdir($this->root . 'theme_child/Configuration/PageTS/Empty');
        $this->write('theme_child/ext_localconf.php', "<?php\n// 'EXT:theme_base/Configuration/TypoScript/setup.txt'\n");
        $this->write('foreign/Configuration/TypoScript/setup.txt', "foreign = 1\n");
        $this->write('foreign/Configuration/TypoScript/setup.typoscript', "foreign = 1\n");
        $this->write('foreign/Configuration/TypoScript/legacy.txt', "legacy = 1\n");
    }

    protected function tearDown(): void
    {
        $this->remove($this->root);
        parent::tearDown();
    }

    private function write(string $file, string $content): void
    {
        if (!is_dir(dirname($this->root . $file))) {
            mkdir(dirname($this->root . $file), 0777, true);
        }
        file_put_contents($this->root . $file, $content);
    }

    private function read(string $file): string
    {
        return (string)file_get_contents($this->root . $file);
    }

    private function remove(string $path): void
    {
        if (is_dir($path)) {
            foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $entry) {
                $this->remove(rtrim($path, '/') . '/' . $entry);
            }
            rmdir($path);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }

    private function convert(): \KayStrobach\Themes\Migration\ConversionPlan
    {
        $converter = new IncludeTypoScriptConverter();
        $paths = [
            'theme_base' => $this->root . 'theme_base',
            'theme_child' => $this->root . 'theme_child',
            'foreign' => $this->root . 'foreign',
            // the root package of a project contains all others
            'project/root' => $this->root,
        ];
        $plan = $converter->plan($paths, ['theme_base', 'theme_child']);
        $converter->apply($plan);
        return $plan;
    }

    public function testIncludesBecomeImportsAndFilesAreRenamed(): void
    {
        $plan = $this->convert();

        self::assertSame(implode("\n", [
            "@import 'EXT:theme_base/Configuration/TypoScript/setup.typoscript'",
            "@import 'EXT:theme_child/Configuration/TypoScript/Library/*.setup.typoscript'",
            "@import 'EXT:theme_child/Configuration/TypoScript/Library/Sub/*.setup.typoscript'",
            "@import 'EXT:theme_child/Configuration/Elements/*.typoscript'",
            "[extension.isLoaded('foreign') == 1]",
            "@import 'EXT:foreign/Configuration/TypoScript/setup.typoscript'",
            '[END]',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/legacy.txt">',
            '[page["uid"] == 1]',
            "\t@import './Library/b.setup.typoscript'",
            '[END]',
            // comments stay, references to renamed files are updated
            '#<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme_base/Configuration/TypoScript/constants.typoscript">',
            '',
        ]), $this->read('theme_child/Configuration/TypoScript/setup.typoscript'));
        self::assertSame(
            "@import 'EXT:theme_child/Configuration/TypoScript/Library/*.constants.typoscript'\n",
            $this->read('theme_child/Configuration/TypoScript/constants.typoscript')
        );
        self::assertSame(implode("\n", [
            "@import 'EXT:theme_base/Configuration/PageTS/tsconfig.typoscript'",
            "@import 'EXT:theme_child/Configuration/PageTS/Library/*.tsconfig'",
            "@import 'EXT:theme_child/Configuration/PageTS/Empty/*.tsconfig'",
            '',
        ]), $this->read('theme_child/Configuration/PageTS/tsconfig.typoscript'));

        foreach ([
            'theme_base/Configuration/TypoScript/setup.typoscript',
            'theme_base/Configuration/TypoScript/constants.typoscript',
            'theme_child/Configuration/TypoScript/Library/a.setup.typoscript',
            'theme_child/Configuration/TypoScript/Library/a.constants.typoscript',
            'theme_child/Configuration/TypoScript/Library/Sub/c.setup.typoscript',
            'theme_child/Configuration/PageTS/Library/mod.tsconfig',
        ] as $file) {
            self::assertFileExists($this->root . $file);
        }
        self::assertFileExists($this->root . 'foreign/Configuration/TypoScript/legacy.txt', 'only the extensions to convert are changed');
        self::assertStringContainsString("'EXT:theme_base/Configuration/TypoScript/setup.typoscript'", $this->read('theme_child/ext_localconf.php'));
        self::assertCount(1, $plan->warnings);
        self::assertStringContainsString('legacy.txt" can not be loaded with @import', $plan->warnings[0]);
    }

    public function testWildcardIsOnlyUsedWhenItLoadsExactlyTheIncludedFiles(): void
    {
        $this->write('theme_child/Configuration/Elements/z.setupts', "z = 1\n");
        $this->write('theme_child/Configuration/TypoScript/setup.typoscript', '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/Elements/" extensions="typoscript,setupts">' . "\n");

        $this->convert();

        // TYPO3 12 order: x.typoscript, y.typoscript, z.setupts
        self::assertSame(implode("\n", [
            "@import 'EXT:theme_child/Configuration/Elements/x.typoscript'",
            "@import 'EXT:theme_child/Configuration/Elements/y.typoscript'",
            "@import 'EXT:theme_child/Configuration/Elements/z.setup.typoscript'",
            '',
        ]), $this->read('theme_child/Configuration/TypoScript/setup.typoscript'));
    }

    public function testMissingDirectoriesAndFilesWithAnotherEnding(): void
    {
        $this->write('foreign/Configuration/TypoScript/page.css.typoscript', "css = 1\n");
        $this->write('theme_child/Configuration/TypoScript/setup.typoscript', implode("\n", [
            '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:theme_child/Configuration/Missing/" extensions="setupts,txt">',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/page.css.setupts">',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/gone.setupts">',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:not_installed/Configuration/TypoScript/setup.typoscript" condition="extension.isLoaded(\'not_installed\') == 1">',
            '',
        ]));

        $plan = $this->convert();

        self::assertSame(implode("\n", [
            "@import 'EXT:theme_child/Configuration/Missing/*.setup.typoscript'",
            "@import 'EXT:theme_child/Configuration/Missing/*.typoscript'",
            "@import 'EXT:foreign/Configuration/TypoScript/page.css.typoscript'",
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:foreign/Configuration/TypoScript/gone.setupts">',
            "[extension.isLoaded('not_installed') == 1]",
            "@import 'EXT:not_installed/Configuration/TypoScript/setup.typoscript'",
            '[END]',
            '',
        ]), $this->read('theme_child/Configuration/TypoScript/setup.typoscript'));
        self::assertStringContainsString('EXT:theme_child/Configuration/TypoScript/setup.typoscript:3: kept, "FILE:EXT:foreign/Configuration/TypoScript/gone.setupts" not found', implode("\n", $plan->warnings));
    }

    public function testSecondRunChangesNothing(): void
    {
        $this->convert();
        $plan = (new IncludeTypoScriptConverter())->plan([
            'theme_base' => $this->root . 'theme_base',
            'theme_child' => $this->root . 'theme_child',
            'foreign' => $this->root . 'foreign',
            'project/root' => $this->root,
        ], ['theme_base', 'theme_child']);

        self::assertTrue($plan->isEmpty());
    }
}
