<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\EventListener\ResolveNestedConstants;
use KayStrobach\Themes\EventListener\RewriteLegacyExtensionPaths;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\Tokenizer\LosslessTokenizer;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Event\ModifyTypoScriptConstantsEvent;

class ConstantsTest extends AbstractThemesTestCase
{
    /**
     * @return array<string, string>
     */
    private function resolve(string $constants): array
    {
        $ast = $this->get(AstBuilder::class)->build((new LosslessTokenizer())->tokenize($constants), new RootNode());
        $event = new ModifyTypoScriptConstantsEvent($ast);
        (new ResolveNestedConstants())($event);
        return $event->getConstantsAst()->flatten();
    }

    public function testConstantsReferencingOtherConstantsAreResolved(): void
    {
        // TYPO3 up to v11 did this, the parser of v12 substitutes constants only in the setup
        $constants = $this->resolve(
            "themes.configuration.siteName = My school\n"
            . "siteTitle = {\$themes.configuration.siteName}\n"
            . "headline = Welcome to {\$siteTitle}!\n"
        );
        self::assertSame('My school', $constants['siteTitle']);
        self::assertSame('Welcome to My school!', $constants['headline'], 'nested over two levels');
    }

    public function testEmptyConstantIsResolvedToAnEmptyString(): void
    {
        $constants = $this->resolve("themes.configuration.siteName =\nsiteTitle = {\$themes.configuration.siteName}\n");
        self::assertSame('', $constants['siteTitle']);
    }

    public function testUndefinedAndSelfReferencingConstantsStay(): void
    {
        $constants = $this->resolve("a = {\$undefined}\nb = x{\$b}\n");
        self::assertSame('{$undefined}', $constants['a']);
        self::assertStringStartsWith('x', $constants['b'], 'self reference ends after a few passes');
    }

    public function testTypo3confExtPathsAreRewrittenToThePublicUrl(): void
    {
        $publicPath = '/' . trim(PathUtility::getPublicResourceWebPath('EXT:themes/Resources/Public/'), '/') . '/';
        $rewrite = new RewriteLegacyExtensionPaths();
        self::assertSame($publicPath . 'Fonts/', $rewrite->rewriteValue('/typo3conf/ext/themes/Resources/Public/Fonts/'));
        self::assertSame('url(' . $publicPath . 'Icons/a.svg)', $rewrite->rewriteValue('url(../../typo3conf/ext/themes/Resources/Public/Icons/a.svg)'));
        self::assertSame('/typo3conf/ext/not_loaded/Resources/Public/a.css', $rewrite->rewriteValue('/typo3conf/ext/not_loaded/Resources/Public/a.css'));
        self::assertSame('/typo3conf/ext/themes/Resources/Private/a.css', $rewrite->rewriteValue('/typo3conf/ext/themes/Resources/Private/a.css'));
    }
}
