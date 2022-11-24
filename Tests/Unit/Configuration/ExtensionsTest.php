<?php

declare(strict_types=1);

namespace KayStrobach\Tests\Unit\Configuration;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ExtensionsTest.
 */
class ExtensionsTest extends UnitTestCase
{
    /**
     * @test
     */
    public function checkForConflictingExtensionTemplavoila()
    {
        self::assertFalse(
            ExtensionManagementUtility::isLoaded('templavoila'),
            'Sadly templavoila can conflict with themes, this is not an hard error, but can cause problems with ext:vhs, fluidcontent, fluidpages'
        );
    }
}
