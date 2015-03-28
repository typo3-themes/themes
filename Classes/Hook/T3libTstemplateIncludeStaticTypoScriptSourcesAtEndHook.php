<?php

namespace KayStrobach\Themes\Hook;

// class for: $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][]

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook
 *
 * Hook to include the TypoScript during the rendering
 *
 * @package KayStrobach\Themes\Hook
 */
class T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook {

	/**
	 * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
	 *
	 * @param array Array of parameters from the parent class. Includes idList, templateId, pid, and row.
	 * @param \TYPO3\CMS\Core\TypoScript\TemplateService Reference back to parent object, t3lib_tstemplate or one of its subclasses.
	 * @return	void
	 */
	public static function main(&$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj) {
		$idList = $params['idList'];
		$templateId = $params['templateId'];
		$pid = $params['pid'];
		$row = $params['row'];

		if ($templateId === $idList) {

			$tRow = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'sys_template', 'pid=' . (int) $pid);
			$row['tx_themes_skin'] = $tRow['tx_themes_skin'];

			// Call hook for possible manipulation of current skin.
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hook/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'])) {
				$tempParamsForHook = array('theme' => &$row['tx_themes_skin']);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hook/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['setTheme'] as $userFunc) {
					$row['tx_themes_skin'] = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $ref = NULL);
				}
			}

			/**
			 * @var $themeRepository \KayStrobach\Themes\Domain\Repository\ThemeRepository
			 */
			$themeRepository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');
			$theme = $themeRepository->findByUid($row['tx_themes_skin']);
			if ($theme !== NULL) {
				$theme->addTypoScriptForFe($params, $pObj);
			}

			// @todo add hook to inject template overlays, e.g. for previewed constants before save ...
			// Call hook for possible manipulation of current skin. constants
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hook/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/themes/Classes/Hook/T3libTstemplateIncludeStaticTypoScriptSourcesAtEndHook.php']['modifyTS'] as $userFunc) {
					$themeItem = GeneralUtility::callUserFunction($userFunc, $tempParamsForHook, $pObj);
					$pObj->processTemplate(
						$themeItem,
						$params['idList'] . ',themes_modifyTsOverlay',
						$params['pid'],
						'themes_themes_modifyTsOverlay',
						$params['templateId']
					);
				}
			}
		}
	}

}
