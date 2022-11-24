<?php

declare(strict_types=1);

namespace KayStrobach\Tests\Unit\Domain\Repository;

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ThemeRepositoryTest.
 */
class ThemeRepositoryTest extends UnitTestCase
{
    /**
     * @var ThemeRepository
     */
    protected ThemeRepository $fixture;

    public function setUp(): void
    {
        $this->fixture = new ThemeRepository();
    }

    public function tearDown(): void
    {
    }

    /**
     * @test
     */
    public function findAllCountTest()
    {
        self::markTestSkipped('Needs to be moved to functionaltesting');

        return;
        self::assertGreaterThanOrEqual(
            1,
            count($this->fixture->findAll()),
            'No Themes found :('
        );
    }

    /**
     * @test
     */
    public function findAllTypeTest()
    {
        self::assertGreaterThanOrEqual(
            'array',
            gettype($this->fixture->findAll()),
            'themes does not contain an array of themes'
        );
    }

    /**
     * @test
     */
    public function checkForOldHookUsage()
    {
        self::assertFalse(
            !empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init']),
            'One of your extensions is still using the old hook, please repair that'
        );
    }

    /**
     * @test
     */
    public function checkForNewHookUsage()
    {
        self::markTestSkipped('Needs to be moved to functionaltesting');

        return;
        self::assertTrue(
            !empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init']),
            'You have no theme provider registered, themes itself should register one!'
        );
    }
}
