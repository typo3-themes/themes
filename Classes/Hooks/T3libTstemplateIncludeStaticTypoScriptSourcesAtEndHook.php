<?php

namespace KayStrobach\Themes\Hooks;

// class for: $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.
 *
 * Hook to include the TypoScript during the rendering
 */
class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook
{
    /**
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array array of parameters from the parent class. Includes idList, templateId, pid, and row.
     * @param \TYPO3\CMS\Core\TypoScript\TemplateService Reference back to parent object, t3lib_tstemplate or one of its subclasses.
     *
     * @return void
     */
    public static function main(&$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj)
    {
        $idList = $params['idList'];
        $templateId = $params['templateId'];
        $pid = $params['pid'];
        $row = $params['row'];
        if ($templateId === $idList) {
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_template');
            $queryBuilder->select('*')
                ->from('sys_template')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid', $queryBuilder->createNamedParameter((int)$pid, \PDO::PARAM_INT)
                    )
                );
            /** @var  \Doctrine\DBAL\Driver\Statement $statement */
            $statement = $queryBuilder->execute();
            if($statement->rowCount()>0) {
                $tRow = $statement->fetch();
                $themeIdentifier = $tRow['tx_themes_skin'];
                //
                // Call hook for possible manipulation of current skin.
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'])) {
                    $tempParamsForHook = ['theme' => $themeIdentifier];
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'] as $userFunc) {
                        $themeIdentifier = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $ref = null);
                    }
                }
                if (empty($themeIdentifier)) {
                    $themeIdentifier = $tRow['tx_themes_skin'];
                }
                /** @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository */
                $themeRepository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');
                /** @var \KayStrobach\Themes\Domain\Model\Theme $theme */
                $theme = $themeRepository->findByUid($themeIdentifier);
                if ($theme === null) {
                    // fallback if the hook returns a undefined theme
                    $theme = $themeRepository->findByUid($tRow['tx_themes_skin']);
                }
                if ($theme !== null) {
                    $theme->addTypoScriptForFe($params, $pObj);
                }
                // @todo add hook to inject template overlays, e.g. for previewed constants before save ...
                // Call hook for possible manipulation of current skin. constants
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'])) {
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hooks/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'] as $userFunc) {
                        $themeItem = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $pObj);
                        $pObj->processTemplate(
                            $themeItem,
                            $params['idList'].',themes_modifyTsOverlay',
                            $params['pid'],
                            'themes_themes_modifyTsOverlay',
                            $params['templateId']
                        );
                    }
                }
            }
        }
    }
}
