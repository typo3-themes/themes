<?php

declare(strict_types=1);

namespace KayStrobach\Tests\Unit\Domain\Model;

use KayStrobach\Themes\Domain\Model\Theme;
use TYPO3\CMS\Install\Configuration\Exception;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ThemeTest.
 */
class ThemeTest extends UnitTestCase
{
    /**
     * @var Theme
     */
    protected Theme $fixture;

    public function setUp(): void
    {
        $this->fixture = new Theme('themes_theme_test');
    }

    public function tearDown(): void
    {
    }

    /**
     * @test
     * @throws Exception
     */
    public function addTypoScriptForFeTest()
    {
        $params = [

        ];
        $observer = $this->createMock('Observer');
        $observer->expects(self::once())
            ->method('processTemplate');

        $this->fixture->addTypoScriptForFe($params, $observer);
    }
}
