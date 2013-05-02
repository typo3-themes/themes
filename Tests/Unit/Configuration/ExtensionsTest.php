<?php

class Tx_Themes_Configuration_ExtensionsTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @test
	 */
	public function checkForConflictingExtensionTemplavoila() {
		$this->assertSame(FALSE, t3lib_extMgm::isLoaded('templavoila'), 'Sadly templavoila can conflict with themes, this is not an hard error, but can cause problems with ext:vhs, fluidcontent, fluidpages');
	}


}
?>