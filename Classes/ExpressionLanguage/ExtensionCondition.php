<?php

declare(strict_types=1);

namespace KayStrobach\Themes\ExpressionLanguage;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class ExtensionCondition
{
    /**
     * @param string $extensionKey
     * @return bool
     */
    public function isLoaded(string $extensionKey): bool
    {
        return ExtensionManagementUtility::isLoaded($extensionKey);
    }
}
