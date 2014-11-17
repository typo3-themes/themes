<?php

namespace KayStrobach\Themes\ViewHelpers\Widget\Controller;

/**
 * Controller for Language Menu Widget
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
use SJBR\StaticInfoTables\Domain\Repository\LanguageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LanguageMenuController
 *
 * @package KayStrobach\Themes\ViewHelpers\Widget\Controller
 */
class LanguageMenuController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Language Repository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository
	 * @inject
	 */
	protected $languageRepository;

	/**
	 * ContentObjectRenderer
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 * @inject
	 */
	protected $contentObjectRenderer;

	/**
	 * @param \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository
	 * @return void
	 */
	public function injectLanguageRepository(LanguageRepository $languageRepository) {
		$this->languageRepository = $languageRepository;
	}

	/**
	 * @return void
	 */
	public function indexAction() {

		$menu = array();
		$availableLanguages = explode(',', '0,' . $this->widgetConfiguration['availableLanguages']);
		$availableLanguages = array_unique($availableLanguages);
		$currentLanguageUid = (int) $this->widgetConfiguration['currentLanguageUid'];
		$defaultLanguageIsoCodeShort = $this->widgetConfiguration['defaultLanguageIsoCodeShort'];
		$defaultLanguageLabel = $this->widgetConfiguration['defaultLanguageLabel'];
		$defaultLanguageFlag = $this->widgetConfiguration['defaultLanguageFlag'];
		$flagIconPath = $this->widgetConfiguration['flagIconPath'];
		$flagIconFileExtension = $this->widgetConfiguration['flagIconFileExtension'];

		if (!empty($availableLanguages)) {
			foreach ($availableLanguages as $languageUid) {

				$menuEntry = array();
				$menuEntry['L'] = $languageUid;
				$menuEntry['pageUid'] = $GLOBALS['TSFE']->id;
				$class = 'unknown';
				$label = 'unknown';
				$flag = 'unknown';
				$hasTranslation = TRUE;

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
				$menuEntry['flag'] = $flag;
				$menuEntry['hasTranslation'] = $hasTranslation;
				$menu[] = $menuEntry;
			}
		}

		$this->view->assign('menu', $menu);
		$this->view->assign('flagIconPath', $flagIconPath);
		$this->view->assign('flagIconFileExtension', $flagIconFileExtension);
	}

	/**
	 * Get the data of a sys language
	 * @param $uid \int Language uid
	 * @return \array Language data array
	 */
	protected function getSysLanguage($uid = 0) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, title, flag, static_lang_isocode', 'sys_language', 'uid=' . ((int) $uid)
		);
		$sysLanguage = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $sysLanguage;
	}

	/**
	 * Check if a translation of a page is available
	 * @param $pid \int Page id
	 * @param $languageUid \int Language uid
	 * @return bool
	 */
	protected function hasTranslation($pid, $languageUid) {

		$enableFieldsSql  = $this->contentObjectRenderer->enableFields('pages_language_overlay');
		//$visibleSql  = ' deleted=0 AND t3ver_state<=0 AND hidden=0 ';
		//$startEndSql = ' AND (starttime<=' . time() . ' AND (endtime=0 OR endtime >=' . time() . ')) ';
		$languageSql = ' pid=' . ((int)$pid) . ' AND `sys_language_uid` =' . ((int)$languageUid) . ' ';
		$where = $languageSql . $enableFieldsSql;
		//$visibleSql.$startEndSql;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(uid)', 'pages_language_overlay', $where);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return ($row[0] > 0);
	}
}
