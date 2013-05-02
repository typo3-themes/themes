<?php

class Tx_Themes_Domain_Model_ThemeTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Themes_Domain_Model_Theme
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Themes_Domain_Model_Theme('themes_theme_test');
	}

	public function tearDown() {

	}
	/**
	 * @test
	 */
	public function addTypoScriptForFeTest() {
		$params = array(

		);
		$observer = $this->getMock('Observer', array('processTemplate'));
		$observer->expects($this->once())
			->method('processTemplate');

		$this->fixture->addTypoScriptForFe($params, $observer);
	}
}