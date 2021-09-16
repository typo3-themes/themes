<?php

namespace KayStrobach\Themes\Utilities;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CheckPageUtility.
 */
class CheckPageUtility
{
    /**
     * @param $pid
     *
     * @return bool
     */
    public static function hasTheme($pid)
    {
        $hasTheme = false;
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_template');
        $queryBuilder->select('*')
            ->from('sys_template')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter((int) $pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'root',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'tx_themes_skin',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $hasTheme = true;
        }
        return $hasTheme;
    }

    /**
     * @param $pid
     *
     * @return bool
     */
    public static function hasThemeableSysTemplateRecord($pid)
    {
        self::hasTheme($pid);
        $themeable = false;
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_template');
        $queryBuilder->select('*')
            ->from('sys_template')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter((int) $pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'root',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $themeable = true;
        }
        return $themeable;
    }

    /**
     * @param $pid
     *
     * @return bool|int
     */
    public static function getThemeableSysTemplateRecord($pid)
    {
        $themeable = false;
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_template');
        $queryBuilder->select('*')
            ->from('sys_template')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter((int) $pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'root',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $row = $statement->fetch();
            $themeable = $row['uid'];
        }
        return $themeable;
    }
}
