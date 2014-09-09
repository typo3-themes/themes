<?php

namespace KayStrobach\Tests\Unit\Domain\Repository;

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Class ThemeRepositoryTest
 * @package KayStrobach\Tests\Unit\Domain\Repository
 */
class ThemeRepositoryTest extends BaseTestCase {
	/**
	 * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new ThemeRepository();
	}

	public function tearDown() {

	}
	/**
	 * @test
	 */
	public function findAllCountTest() {
		$this->markTestSkipped('Needs to be moved to functionaltesting');
		return;
		$this->assertGreaterThanOrEqual(
			1,
			count($this->fixture->findAll()),
			'No Themes found :('
		);
	}
	/**
	 * @test
	 */
	public function findAllTypeTest() {
		$this->assertGreaterThanOrEqual(
			'array',
			gettype($this->fixture->findAll()),
			'themes does not contain an array of themes'
		);
	}

	/**
	 * @test
	 */
	public function checkForOldHookUsage() {
		$this->assertSame(
			FALSE,
			is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init']),
			'One of your extensions is still using the old hook, please repair that'
		);
	}

	/**
	 * @test
	 */
	public function checkForNewHookUsage() {
		$this->markTestSkipped('Needs to be moved to functionaltesting');
		return;
		$this->assertSame(
			TRUE,
			is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init']),
			'You have no theme provider registered, themes itself should register one!'
		);
	}
}