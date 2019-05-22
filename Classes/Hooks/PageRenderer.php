<?php

namespace KayStrobach\Themes\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class/Function which adds the necessary ExtJS and pure JS stuff for themes.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class PageRenderer implements SingletonInterface
{
    /**
     * wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess).
     *
     * @param array $parameters An array of available parameters
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer The parent object that triggered this hook
     *
     * @return void
     */
    public function addJSCSS(array $parameters, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer)
    {
        // Add javascript
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Themes/ThemesBackendTca');
        // Add css
        $extensionFile = 'Resources/Public/Stylesheet/ThemesBackendTca.css';
        $absolutePath = ExtensionManagementUtility::extPath('themes', $extensionFile);
        $filename = PathUtility::getAbsoluteWebPath($absolutePath);
        $pageRenderer->addCssFile($filename, 'stylesheet', 'screen');
    }
}
