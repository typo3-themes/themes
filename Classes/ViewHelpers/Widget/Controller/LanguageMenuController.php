<?php

namespace KayStrobach\Themes\ViewHelpers\Widget\Controller;

/**
 * Controller for Language Menu Widget
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
	 * @param \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository
	 * @return void
	 */
	public function injectLanguageRepository(\SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository) {
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

				// Is active language
				$menuEntry['active'] = ((int) $currentLanguageUid === (int) $languageUid);

				// Is default language
				if ((int) $languageUid === 0) {
					$class = $defaultLanguageIsoCodeShort != '' ? $defaultLanguageIsoCodeShort : 'en';
					$label = $defaultLanguageLabel != '' ? $defaultLanguageLabel : 'English';
					$flag = $defaultLanguageFlag != '' ? $defaultLanguageFlag : 'gb';
				} else if ($sysLanguage = $this->getSysLanguage($languageUid)) {

					if (!($this->languageRepository instanceof \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository)) {
						$this->languageRepository = GeneralUtility::makeInstance('SJBR\\StaticInfoTables\\Domain\\Repository\\LanguageRepository');
					}
					$languageObject = $this->languageRepository->findByIdentifier($sysLanguage['static_lang_isocode']);
					if ($languageObject instanceof \SJBR\StaticInfoTables\Domain\Model\Language) {
						$class = $languageObject->getIsoCodeA2();
						$label = $languageObject->getNameEn();
					}
					$flag = $sysLanguage['flag'];
				}

				$menuEntry['label'] = $label;
				$menuEntry['class'] = strtolower($class);
				$menuEntry['flag'] = $flag;
				$menu[] = $menuEntry;
			}
		}

		$this->view->assign('menu', $menu);
		$this->view->assign('flagIconPath', $flagIconPath);
		$this->view->assign('flagIconFileExtension', $flagIconFileExtension);
	}

	/**
	 * @todo missing docblock
	 */
	protected function getSysLanguage($uid = 0) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, title, flag, static_lang_isocode', 'sys_language', 'uid=' . ((int) $uid)
		);
		$sysLanguage = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $sysLanguage;
	}

}
