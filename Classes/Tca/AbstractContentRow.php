<?php

namespace KayStrobach\Themes\Tca;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract for ContentRow.
 */
abstract class AbstractContentRow
{
    protected $ctypeProperties = [];
    protected $defaultProperties = [];

    protected function getMissedFields($values, $valuesAvailable)
    {
        $missedField = '';
        $missedClasses = array_diff($values, $valuesAvailable);
        $missedClass = htmlspecialchars(implode(', ', $missedClasses));
        if (!empty($missedClass)) {
            $label = $this->getLanguageService()->sL('LLL:EXT:themes/Resources/Private/Language/locallang.xlf:unavailable_classes');
            $missedField = '<div class="alert alert-danger" role="alert"><strong>'.$label.':</strong> '.$missedClass.'</div>';
        }

        return $missedField;
    }

    protected function getMergedConfiguration($pid, $node, $cType)
    {
        // Get configuration ctype specific configuration
        $cTypeConfig = $this->getBeUser()->getTSConfig(
            'themes.content.'.$node.'.'.$cType,
            BackendUtility::getPagesTSconfig($pid)
        );
        $this->ctypeProperties = $cTypeConfig['properties'];
        // Get default configuration
        $defaultConfig = $this->getBeUser()->getTSConfig(
            'themes.content.'.$node.'.default',
            BackendUtility::getPagesTSconfig($pid)
        );
        $this->defaultProperties = $defaultConfig['properties'];
        // Merge configurations
        $config = array_replace_recursive($cTypeConfig, $defaultConfig);

        return $config;
    }

    protected function getPidFromParentContentElement($pid)
    {
        $parentPid = 0;
        //
        // negative uid_pid values indicate that the element has been inserted after an existing element
        // so there is no pid to get the backendLayout for and we have to get that first
        //
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(-((int) $pid), \PDO::PARAM_INT)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $row = $statement->fetch();
            $parentPid = $row['pid'];
        }
        return $parentPid;
    }

    /**
     * Checks if a backend user is an admin user.
     *
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->getBeUser()->isAdmin();
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBeUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
