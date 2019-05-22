<?php

namespace KayStrobach\Themes\Slots;

use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Backend\Configuration\TsConfigParser;

/**
 * This class automatically adds the theme TSConfig for the current page
 * to the Page TSConfig either by using a signal slot.
 */
class BackendUtilitySlot extends TsConfigParser
{

    /**
     * Selected/activated extensions in Theme (selected by sys_template)
     * @var array
     */
    protected $themeExtensions = [];

    /**
     * Selected/activated features in Theme (selected by sys_template)
     * @var array
     */
    protected $themeFeatures = [];

    /**
     * Retrieves the theme TSConfig for the given page.
     *
     * @param $typoscriptDataArray
     * @param int $pageUid
     * @param $rootLine
     * @param $returnPartArray
     *
     * @return array The found TSConfig or an empty string.
     */
    public function getPagesTsConfigPreInclude($typoscriptDataArray, $pageUid, $rootLine, $returnPartArray)
    {
        $pageUid = (int)$pageUid;
        if ($pageUid === 0) {
            return [];
        }
        /** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
        $themeRepository = GeneralUtility::makeInstance('KayStrobach\Themes\\Domain\\Repository\\ThemeRepository');
        $theme = $themeRepository->findByPageOrRootline($pageUid);
        if (!isset($theme)) {
            return [];
        }
        // Append Theme tsconfig.txt
        $defaultDataArray['defaultPageTSconfig'] = array_shift($typoscriptDataArray);
        //
        // Fetch Theme Extensions and Features
        $this->fetchThemeExtensionsAndFeatures($pageUid);
        //
        // Additional TypoScript for extensions
        if (count($this->themeExtensions) > 0) {
            foreach ($this->themeExtensions as $extension) {
                $tsconfig = $this->getTypoScriptDataForProcessing($extension, 'extension');
                if ($tsconfig !== '') {
                    array_unshift($typoscriptDataArray, $tsconfig);
                }
            }
        }
        //
        // Additional TypoScript for features
        if (count($this->themeFeatures) > 0) {
            foreach ($this->themeFeatures as $feature) {
                $tsconfig = $this->getTypoScriptDataForProcessing($feature, 'feature');
                if ($tsconfig !== '') {
                    array_unshift($typoscriptDataArray, $tsconfig);
                }
            }
        }
        //
        array_unshift($typoscriptDataArray, $theme->getTypoScriptConfig());
        $typoscriptDataArray = $defaultDataArray + $typoscriptDataArray;
        return [
            $typoscriptDataArray,
            $pageUid,
            $rootLine,
            $returnPartArray,
        ];
    }

    /**
     * Fetches the selected Extensions and Features by the Theme
     * @param $pageUid
     */
    protected function fetchThemeExtensionsAndFeatures($pageUid)
    {
        //
        // Find sys_template recursive
        $rootline = BackendUtility::BEgetRootLine($pageUid);
        foreach ($rootline as $page) {
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_template');
            $queryBuilder->select('*')
                ->from('sys_template')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq(
                            'pid', $queryBuilder->createNamedParameter((int)$page['uid'], \PDO::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq('root', '1')
                    )
                );
            /** @var  \Doctrine\DBAL\Driver\Statement $statement */
            $statement = $queryBuilder->execute();
            if ($statement->rowCount()>0) {
                $tRow = $statement->fetch();
                $this->themeExtensions = GeneralUtility::trimExplode(',', $tRow['tx_themes_extensions'], true);
                $this->themeFeatures = GeneralUtility::trimExplode(',', $tRow['tx_themes_features'], true);
                break;
            }
        }
    }

    /**
     * @param $key string Key of the Extension or Feature
     * @param $type string Typ can be either extension or feature.
     * @return array
     */
    protected function getTypoScriptDataForProcessing($key, $type='extension')
    {
        $relPath = '';
        $keyParts = explode('_', $key);
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($keyParts[0]);
        $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
        if ($type === 'feature') {
            $relPath = $extensionPath . 'Configuration/PageTS/Features/' . $keyParts[1] . '/';
        } elseif ($type === 'extension') {
            $relPath = $extensionPath . 'Resources/Private/Extensions/' . $keyParts[1] . '/PageTS/';
        }
        //
        $tsconfig = '';
        $constantsFile = GeneralUtility::getFileAbsFileName($relPath . 'tsconfig.txt');
        if (file_exists($constantsFile)) {
            $tsconfig = file_get_contents($constantsFile);
        } else {
            $constantsFile = GeneralUtility::getFileAbsFileName($relPath . 'tsconfig.typoscript');
            if (file_exists($constantsFile)) {
                $tsconfig = file_get_contents($constantsFile);
            }
        }
        return $tsconfig;
    }
}
