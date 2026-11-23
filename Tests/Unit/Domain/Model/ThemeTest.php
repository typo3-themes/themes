<?php

namespace KayStrobach\Tests\Unit\Domain\Model;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ThemeTest.
 */
class ThemeTest extends UnitTestCase
{
    /**
     * @var \KayStrobach\Themes\Domain\Model\Theme
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new \KayStrobach\Themes\Domain\Model\Theme('themes_theme_test');
    }

    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function addTypoScriptForFeTest()
    {
        $params = [

        ];
        $observer = $this->getMock('Observer', ['processTemplate']);
        $observer->expects($this->once())
            ->method('processTemplate');

        $this->fixture->addTypoScriptForFe($params, $observer);
    }
}
