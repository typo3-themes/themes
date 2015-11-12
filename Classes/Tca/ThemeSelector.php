<?php

namespace KayStrobach\Themes\Tca;
use KayStrobach\Themes\Domain\Model\Theme;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ThemeSelector
 *
 * @package KayStrobach\Themes\Tca
 */
class ThemeSelector {

	public function items(&$PA, $fobj) {
		/**
		 * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository $repository
		 */
		$repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

		$themes = $repository->findAll();

		$PA['items'] = array(
			array(
				0 => 'None',
				1 => '',
			)
		);

		/** @var Theme $theme */
		foreach($themes as $theme) {
			$PA['items'][] = array(
				0 => $theme->getTitle(),
				1 => $theme->getExtensionName(),
				2 => $theme->getPreviewImage(),
			);
		}
	}

}
