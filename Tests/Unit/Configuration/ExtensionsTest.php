<?php

namespace KayStrobach\Tests\Unit\Configuration;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Class ExtensionsTest.
 */
class ExtensionsTest extends BaseTestCase
{
    /**
     * @test
     */
    public function checkForConflictingExtensionTemplavoila()
    {
        $this->assertSame(
            false,
            ExtensionManagementUtility::isLoaded('templavoila'),
            'Sadly templavoila can conflict with themes, this is not an hard error, but can cause problems with ext:vhs, fluidcontent, fluidpages'
        );
    }
}
