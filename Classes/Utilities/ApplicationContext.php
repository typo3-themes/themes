<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.03.15
 * Time: 01:07.
 */
namespace KayStrobach\Themes\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApplicationContext
{
    /**
     * @var \TYPO3\CMS\Install\Configuration\FeatureManager
     * @inject
     */
    protected $featureManager;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $this->featureManager = $objectManager->get('TYPO3\CMS\Install\Configuration\FeatureManager');
    }

    /**
     * @return string
     */
    public static function getApplicationContext()
    {
        return (string) GeneralUtility::getApplicationContext();
    }

    /**
     * @return bool
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
     * @return null|\TYPO3\CMS\Install\Configuration\AbstractPreset
     */
    public function isDevelopPresetActive()
    {
        $features = $this->featureManager->getInitializedFeatures([]);
        /* @var \TYPO3\CMS\Install\Configuration\Context\ContextFeature $contextPreset */
        $contextFeature = null;
        foreach ($features as $feature) {
            if ($feature instanceof \TYPO3\CMS\Install\Configuration\Context\ContextFeature) {
                $contextFeature = $feature;
                continue;
            }
        }
        if ($contextFeature === null) {
            return;
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
