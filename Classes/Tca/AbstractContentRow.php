<?php

namespace KayStrobach\Themes\Tca;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract for ContentRow.
 */
abstract class AbstractContentRow extends AbstractFormElement
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
        $pagesTsConfig = BackendUtilityCore::getPagesTSconfig($pid);
        $this->ctypeProperties = [];
        if(is_array($pagesTsConfig['themes.']['content.'][$node . '.'][$cType . '.'])) {
            $this->ctypeProperties['properties'] = $pagesTsConfig['themes.']['content.'][$node . '.'][$cType . '.'];
        }
        $this->defaultProperties = [];
        if(is_array($pagesTsConfig['themes.']['content.'][$node . '.']['default.'])) {
            $this->defaultProperties['properties'] = $pagesTsConfig['themes.']['content.'][$node . '.']['default.'];
        }
        // Merge configurations
        $config = array_replace_recursive($this->ctypeProperties , $this->defaultProperties);
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
     * Checks if a backend user is an admin user and debug mode is enabled.
     *
     * @return bool
     */
    protected function isAdminAndDebug()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] && $this->getBeUser()->isAdmin();
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
