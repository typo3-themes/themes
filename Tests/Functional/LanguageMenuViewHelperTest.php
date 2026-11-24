<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\View\TemplateView;

class LanguageMenuViewHelperTest extends AbstractThemesTestCase
{
    public function testWidgetOfOlderThemesRendersOnlyAComment(): void
    {
        // Fluid widgets are gone, theme_bootstrap still uses <theme:widget.languageMenu>
        $view = new TemplateView($this->get(RenderingContextFactory::class)->create());
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource(
            '{namespace theme=KayStrobach\Themes\ViewHelpers}'
            . '<theme:widget.languageMenu availableLanguages="0,1" currentLanguageUid="0" defaultLanguageLabel="Deutsch" />'
        );
        self::assertStringStartsWith('<!-- Language Menu Widget is deprecated', trim((string)$view->render()));
    }
}
