<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\Utilities\TsParserUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

/**
 * Backend module "Themes": constants with their definition comments are read with the TypoScript parser of v12+,
 * saving changes only the edited lines of the template record.
 */
class ConstantsEditorTest extends AbstractThemesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/constants_editor.csv');
        // as in the backend module: labels of the constants are translated
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($this->setUpBackendUser(1));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['LANG']);
        parent::tearDown();
    }

    private function templateConstants(): string
    {
        return (string)$this->get(ConnectionPool::class)->getConnectionForTable('sys_template')
            ->select(['constants'], 'sys_template', ['uid' => 1])->fetchOne();
    }

    public function testConstantsAndTheirDefinitionsAreRead(): void
    {
        $constants = (new TsParserUtility())->getConstants(1);
        self::assertSame('#ff0000', $constants['linkColor']['value']);
        self::assertSame('Color', $constants['linkColor']['typeCleaned']);
        self::assertSame('Int', $constants['columns']['typeCleaned']);
        self::assertSame('Example', $constants['plugin.tx_example.title']['value']);
        self::assertArrayHasKey('basic', (new TsParserUtility())->getCategories(1));
    }

    public function testSavingChangesOnlyTheEditedLine(): void
    {
        $before = $this->templateConstants();
        (new TsParserUtility())->applyToPid(1, ['linkColor' => '#00ff00'], ['linkColor' => 'checked']);
        self::assertSame(str_replace('linkColor = #ff0000', 'linkColor = #00ff00', $before), $this->templateConstants());
    }

    public function testConstantInsideABlockIsChangedInPlace(): void
    {
        $before = $this->templateConstants();
        (new TsParserUtility())->applyToPid(1, ['plugin.tx_example.title' => 'Changed'], ['plugin.tx_example.title' => 'checked']);
        self::assertSame(str_replace('title = Example', 'title = Changed', $before), $this->templateConstants());
    }

    public function testIntegersAreLimitedToTheirRange(): void
    {
        (new TsParserUtility())->applyToPid(1, ['columns' => '-5'], ['columns' => 'checked']);
        self::assertStringContainsString("columns = 0\n", $this->templateConstants());
    }

    public function testUncheckedConstantIsRemovedFromTheTemplate(): void
    {
        (new TsParserUtility())->applyToPid(1, ['linkColor' => '#ff0000'], []);
        self::assertStringNotContainsString('linkColor =', $this->templateConstants());
        self::assertStringContainsString('columns = 2', $this->templateConstants());
    }
}
