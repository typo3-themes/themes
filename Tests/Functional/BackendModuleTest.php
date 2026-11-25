<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Tests\Functional;

use KayStrobach\Themes\Controller\EditorController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Backend module "Themes": the actions render inside the module frame (ModuleTemplate::renderResponse(),
 * setContent() is gone since TYPO3 v13), with the doc header (icons: IconSize since v13).
 */
class BackendModuleTest extends AbstractThemesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/constants_editor.csv');
        $this->get(ConnectionPool::class)->getConnectionForTable('sys_template')
            ->update('sys_template', ['tx_themes_skin' => 'themes_fixture_txt'], ['uid' => 1]);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($this->setUpBackendUser(1));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['LANG'], $GLOBALS['TYPO3_REQUEST'], $_GET['id']);
        parent::tearDown();
    }

    private function dispatch(string $action, array $arguments = [], int $pageId = 1): ResponseInterface
    {
        $_GET['id'] = (string)$pageId;
        $parameters = new ExtbaseRequestParameters(EditorController::class);
        $parameters->setControllerExtensionName('Themes');
        $parameters->setPluginName('themes');
        $parameters->setControllerName('Editor');
        $parameters->setControllerActionName($action);
        foreach ($arguments as $name => $value) {
            $parameters->setArgument($name, $value);
        }
        $request = (new ServerRequest('https://example.com/typo3/module/themes', 'GET'))
            ->withQueryParams(['id' => $pageId])
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('route', new Route('/module/themes', ['packageName' => 'typo3-themes/themes', '_identifier' => 'themes']))
            ->withAttribute('extbase', $parameters);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        $GLOBALS['TYPO3_REQUEST'] = $request;
        try {
            return $this->get(EditorController::class)->processRequest(new Request($request));
        } catch (PropagateResponseException $exception) {
            return $exception->getResponse();
        }
    }

    private function html(ResponseInterface $response): string
    {
        $html = (string)$response->getBody();
        self::assertSame(200, $response->getStatusCode(), substr(strip_tags($html), 0, 500));
        // module frame of the core with the content of the action
        self::assertStringContainsString('module-docheader', $html);
        self::assertStringContainsString('typo3-themes-module', $html);
        return $html;
    }

    public function testConstantsEditorIsRendered(): void
    {
        $html = $this->html($this->dispatch('index'));
        self::assertStringContainsString('themes test fixture (txt)', $html, 'selected theme');
        self::assertStringContainsString('BackendModule.css', $html, 'stylesheet of the module via EXT: path');
        self::assertStringNotContainsString('typo3-themes/themes/Resources', $html, 'no file system path');
    }

    public function testThemeSelectionIsRendered(): void
    {
        $this->html($this->dispatch('showTheme'));
    }

    public function testThemeDetailsAreRendered(): void
    {
        $html = $this->html($this->dispatch('showThemeDetails', ['theme' => 'themes_fixture_txt']));
        self::assertStringContainsString('data:image/png;base64,', $html, 'screenshot from Meta/Screenshots');
    }

    public function testPageWithoutTemplateLinksToThePageWithTheTheme(): void
    {
        $this->get(ConnectionPool::class)->getConnectionForTable('pages')
            ->insert('pages', ['uid' => 2, 'pid' => 1, 'title' => 'Subpage', 'doktype' => 1]);
        $html = $this->html($this->dispatch('index', [], 2));
        // no stylesheet of EXT:t3skin (gone), no top.jump() (blocked by the Content Security Policy)
        self::assertDoesNotMatchRegularExpression('#/t3skin/|element_message\.css#', $html);
        self::assertStringNotContainsString('top.jump', $html);
        self::assertMatchesRegularExpression('#href="[^"]*module/themes[^"]*id=1#', html_entity_decode($html), 'link to the page with the theme');
    }
}
