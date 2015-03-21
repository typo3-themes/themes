<?php

namespace KayStrobach\Themes\Provider;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TsProvider implements \TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface {
	/**
	 * Adds backend layouts to the given backend layout collection.
	 *
	 * @param DataProviderContext $dataProviderContext
	 * @param BackendLayoutCollection $backendLayoutCollection
	 * @return void
	 */
	public function addBackendLayouts(DataProviderContext $dataProviderContext, BackendLayoutCollection $backendLayoutCollection) {
		$beLayouts = $this->getLayouts();
		foreach ($beLayouts as $beLayoutIdentifier => $beLayout) {
			$backendLayoutObject = $this->createBackendLayout($beLayoutIdentifier, $beLayout);
			$backendLayoutCollection->add($backendLayoutObject);
		}
		$this->getLayouts();
	}

	/**
	 * Gets a backend layout by (regular) identifier.
	 *
	 * @param string $identifier
	 * @param integer $pageId
	 * @return void|BackendLayout
	 */
	public function getBackendLayout($identifier, $pageId) {
		$beLayouts = $this->getLayouts($pageId);
		foreach ($beLayouts as $beLayoutIdentifier => $beLayout) {
			if ($identifier === $beLayoutIdentifier) {
				return $this->createBackendLayout($beLayoutIdentifier, $beLayout);
			}
		}
	}


	/**
	 * @param $identifier
	 * @param $backendLayoutTS
	 * @return BackendLayout
	 */
	protected function createBackendLayout($identifier, $backendLayoutTS) {
		$backendLayoutObject = BackendLayout::create(
			$identifier,
			$backendLayoutTS['name'],
			'backend_layout {' . "\n" . $backendLayoutTS['backend_layout'] . "\n}"
		);
		$icon = GeneralUtility::getFileAbsFileName($backendLayoutTS['icon']);
		if (is_file($icon)) {
			$icon = '../' . str_replace(PATH_site, '', $icon);
			$backendLayoutObject->setIconPath($icon);
		}
		return $backendLayoutObject;
	}

	/**
	 * Get all backendlayouts
	 *
	 * @param null $pageId
	 * @return array
	 */
	protected function getLayouts($pageId = NULL) {
		if($pageId === NULL) {
			$uid = (int) GeneralUtility::_GET('id');
			if($uid === 0) {
				$edit = GeneralUtility::_GP('edit');
				if(array_key_exists('pages', $edit)) {
					$uid = array_pop(array_keys($edit['pages']));
				}
			}
		} else {
			$uid = $pageId;
		}

		/** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $GLOBALS['BE_USER'] */
		$backendlayoutTS = $GLOBALS["BE_USER"]->getTSConfig(
			'backendlayouts',
			BackendUtility::getPagesTSconfig($uid)
		);

		$backendLayouts = array();
		if(isset($backendlayoutTS['properties'])) {
			foreach($backendlayoutTS['properties'] as $key=>$backendLayout) {
				$backendLayouts[substr($key, 0, -1)] = $backendLayout;
			}
		}

		return $backendLayouts;
	}

}
