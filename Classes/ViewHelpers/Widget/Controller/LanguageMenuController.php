<?php

namespace KayStrobach\Themes\ViewHelpers\Widget\Controller;

/*
 * Controller for Language Menu Widget
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
use SJBR\StaticInfoTables\Domain\Repository\LanguageRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
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
     * Language Repository.
     *
     * @var \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository
     * @inject
     */
    protected $languageRepository;

    /**
     * ContentObjectRenderer.
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @inject
     */
    protected $contentObjectRenderer;

    /**
     * @param \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository
     *
     * @return void
     */
    public function injectLanguageRepository(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $menu = [];
        $availableLanguages = explode(',', '0,'.$this->widgetConfiguration['availableLanguages']);
        $availableLanguages = array_unique($availableLanguages);
        $currentLanguageUid = (int) $this->widgetConfiguration['currentLanguageUid'];
        $defaultLanguageIsoCodeShort = $this->widgetConfiguration['defaultLanguageIsoCodeShort'];
        $defaultLanguageLabel = $this->widgetConfiguration['defaultLanguageLabel'];
        $defaultLanguageFlag = $this->widgetConfiguration['defaultLanguageFlag'];
        $flagIconPath = $this->widgetConfiguration['flagIconPath'];
        $flagIconFileExtension = $this->widgetConfiguration['flagIconFileExtension'];

        if (!empty($availableLanguages)) {
            foreach ($availableLanguages as $languageUid) {
                $menuEntry = [];
                $menuEntry['L'] = $languageUid;
                $menuEntry['pageUid'] = $GLOBALS['TSFE']->id;
                $class = 'unknown';
                $label = 'unknown';
                $flag = 'unknown';
                $hasTranslation = true;

                // Is active language
                $menuEntry['active'] = ((int) $currentLanguageUid === (int) $languageUid);

                // Is default language
                if ((int) $languageUid === 0) {
                    $class = $defaultLanguageIsoCodeShort != '' ? $defaultLanguageIsoCodeShort : 'en';
                    $label = $defaultLanguageLabel != '' ? $defaultLanguageLabel : 'English';
                    $flag = $defaultLanguageFlag != '' ? $defaultLanguageFlag : 'gb';
                } elseif (($sysLanguage = $this->getSysLanguage($languageUid))) {
                    if (!($this->languageRepository instanceof LanguageRepository)) {
                        $this->languageRepository = GeneralUtility::makeInstance('SJBR\\StaticInfoTables\\Domain\\Repository\\LanguageRepository');
                    }
                    $languageObject = $this->languageRepository->findByIdentifier($sysLanguage['static_lang_isocode']);
                    if ($languageObject instanceof \SJBR\StaticInfoTables\Domain\Model\Language) {
                        $class = $languageObject->getIsoCodeA2();
                        $label = $languageObject->getNameEn();
                    }
                    $flag = $sysLanguage['flag'];
                    $hasTranslation = $this->hasTranslation($GLOBALS['TSFE']->id, $languageUid);
                }

                $menuEntry['label'] = $label;
                $menuEntry['class'] = strtolower($class);
                $menuEntry['flag'] = strtoupper($flag);
                $menuEntry['hasTranslation'] = $hasTranslation;
                $menu[] = $menuEntry;
            }
        }

        $this->view->assign('menu', $menu);
        $this->view->assign('flagIconPath', $flagIconPath);
        $this->view->assign('flagIconFileExtension', $flagIconFileExtension);
    }

    /**
     * Get the data of a sys language.
     *
     * @param $uid \int Language uid
     *
     * @return \array Language data array
     */
    protected function getSysLanguage($uid = 0)
    {
        $sysLanguage = array();
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_language');
        $queryBuilder->select('uid', 'title', 'flag', 'static_lang_isocode')
            ->from('sys_language')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid', $queryBuilder->createNamedParameter((int) $uid, \PDO::PARAM_INT)
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        if ($statement->rowCount()>0) {
            $sysLanguage = $statement->fetch();
        }
        return $sysLanguage;
    }

    /**
     * Check if a translation of a page is available.
     *
     * @param $pid \int Page id
     * @param $languageUid \int Language uid
     *
     * @return bool
     */
    protected function hasTranslation($pid, $languageUid)
    {
        $hasTranslation = false;
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder->select('uid')
            ->from('pages')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'pid', $queryBuilder->createNamedParameter((int) $pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid', $queryBuilder->createNamedParameter((int) $languageUid, \PDO::PARAM_INT)
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
