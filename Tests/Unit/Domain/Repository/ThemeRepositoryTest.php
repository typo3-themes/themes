<?php

class Tx_Themes_Domain_Repository_ThemeRepositoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Themes_Domain_Repository_ThemeRepository
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Themes_Domain_Repository_ThemeRepository();
	}

	public function tearDown() {

	}
	/**
	 * @test
	 */
	public function findAllCountTest() {
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
}