<?php

namespace KayStrobach\Themes\ViewHelpers\Widget\Controller;

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

/*
 * Controller for Language Menu Widget
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LanguageMenuController.
 */
class LanguageMenuController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var \TYPO3\CMS\Core\Site\SiteFinder
     */
    protected $siteFinder = null;

    /**
     * @param \TYPO3\CMS\Core\Site\SiteFinder $siteFinder
     */
    public function injectSiteFinder(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    /**
     * @return void
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function indexAction()
    {
        $menu = [];
        $pageUid = (int)$GLOBALS['TSFE']->id;
        $languageUid = (int)$GLOBALS['TSFE']->sys_language_uid;
        /** @var Site $siteConfiguration */
        $siteConfiguration = $this->siteFinder->getSiteByPageId($pageUid);
        /** @var SiteLanguage $language */
        foreach ($siteConfiguration->getAllLanguages() as $language) {
            if ($language->isEnabled()) {
                //
                $item = [
                    'L' => $language->getLanguageId(),
                    'pageUid' => $pageUid,
                    'active' => $languageUid === $language->getLanguageId(),
                ];
                //
                // Navigation title, with fallback on title
                $item['label'] = $language->getNavigationTitle();
                //
                // CSS class and flag
                $item['class'] = strtolower($language->getTwoLetterIsoCode());
                $item['flag'] = strtoupper($language->getTwoLetterIsoCode());
                $item['flagIdentifier'] = $language->getFlagIdentifier();
                //
                // Has translation?
                $item['hasTranslation'] = true;
                if ($language->getLanguageId() > 0) {
                    $item['hasTranslation'] = $this->hasTranslation($pageUid, $language->getLanguageId());
                }
                //
                $menu[] = $item;
            }
        }
        $this->view->assign('menu', $menu);
        $this->view->assign('flagIconPath', $this->widgetConfiguration['flagIconPath']);
        $this->view->assign('flagIconFileExtension', $this->widgetConfiguration['flagIconFileExtension']);
    }

    /**
     * Check if a translation of a page is available.
     *
     * @param $pid \int Page id
     * @param $languageUid \int Language uid
     *
     * @return bool
     */
    protected function hasTranslation($pid, $languageUid): bool
    {
        $hasTranslation = false;
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
        /** @var FrontendRestrictionContainer $frontendRestrictionContainer */
        $frontendRestrictionContainer = GeneralUtility::makeInstance(FrontendRestrictionContainer::class);
        $queryBuilder->setRestrictions($frontendRestrictionContainer);
        $queryBuilder->select('uid')
            ->from('pages')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'l10n_parent',
                    $queryBuilder->createNamedParameter((int) $pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter((int) $languageUid, \PDO::PARAM_INT)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $hasTranslation = true;
        }
        return $hasTranslation;
    }
}
