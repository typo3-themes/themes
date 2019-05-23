<?php

namespace KayStrobach\Themes\Utilities;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Configuration\FeatureManager;
use TYPO3\CMS\Install\Configuration\Context\ContextFeature;

class ApplicationContext
{

    /**
     * @var \TYPO3\CMS\Install\Configuration\FeatureManager
     */
    protected $featureManager;

    /**
     * @param \TYPO3\CMS\Install\Configuration\FeatureManager $featureManager
     */
    public function injectFeatureManager(FeatureManager $featureManager)
    {
        $this->featureManager = $featureManager;
    }

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->featureManager = $objectManager->get(FeatureManager::class);
    }

    /**
     * @return string
     */
    public static function getApplicationContext()
    {
        return (string)GeneralUtility::getApplicationContext();
    }

    /**
     * @return bool
     * @throws \TYPO3\CMS\Install\Configuration\Exception
     */
    public static function isDevelopmentModeActive()
    {
        $applicationContext = new self();
        return $applicationContext->isDevelopmentApplicationContext() || $applicationContext->isDevelopPresetActive();
    }

    /**
     * @return bool
     */
    public function isDevelopmentApplicationContext()
    {
        if (GeneralUtility::getApplicationContext()->isDevelopment()) {
            return true;
        }
        return false;
    }

    /**
     * @throws \TYPO3\CMS\Install\Configuration\Exception
     *
     * @return boolean
     */
    public function isDevelopPresetActive()
    {
        $features = $this->featureManager->getInitializedFeatures([]);
        /* @var \TYPO3\CMS\Install\Configuration\Context\ContextFeature $contextPreset */
        $contextFeature = null;
        foreach ($features as $feature) {
            if ($feature instanceof ContextFeature) {
                $contextFeature = $feature;
                continue;
            }
        }
        if ($contextFeature === null) {
            return false;
        }
        $activePreset = null;
        $presets = $contextFeature->getPresetsOrderedByPriority();
        foreach ($presets as $preset) {
            /** @var \TYPO3\CMS\Install\Configuration\AbstractPreset $preset */
            if ($preset->isActive()) {
                $activePreset = $preset;
                continue;
            }
        }
        if ($activePreset && $activePreset->getName() === 'Development') {
            return true;
        }
        return false;
    }

}
