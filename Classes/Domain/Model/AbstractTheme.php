<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Domain\Model;

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

use KayStrobach\Themes\Utilities\ApplicationContext;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Install\Configuration\Exception;

/**
 * Class AbstractTheme.
 *
 * @todo get rid of getExtensionname, use EXT:extname as theme name to avoid conflicts in the database
 */
class AbstractTheme extends AbstractEntity
{
    protected string $title;

    /**
     * @var array
     */
    protected array $author = [];
    protected string $description;
    protected string $extensionName;
    protected string $version = '';
    protected string $previewImage;
    protected string $pathTyposcript;
    protected string $pathTyposcriptConstants;
    protected string $pathTsConfig;
    protected string $constants = '';
    protected string $setup = '';

    /**
     * @var array
     */
    protected array $metaInformation = [];

    /**
     * Constructs a new Theme.
     *
     * @param string $extensionName
     * @api
     */
    public function __construct(string $extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * Returns the title.
     *
     * @return string
     *
     * @api
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the description.
     *
     * @return string
     *
     * @api
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if the previewImage exists.
     * @return bool
     */
    public function getPreviewImageExists(): bool
    {
        return file_exists(GeneralUtility::getFileAbsFileName($this->previewImage));
    }

    /**
     * @return array
     */
    public function getAllPreviewImages(): array
    {
        return [
            [
                'file' => $this->getPreviewImage(),
                'caption' => '',
            ],
        ];
    }

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getPreviewImage(): string
    {
        return $this->previewImage;
    }

    /**
     * @return array
     */
    public function getMetaInformation(): array
    {
        return $this->metaInformation;
    }

    public function setMetaInformation(array $metaInformation)
    {
        $this->metaInformation = $metaInformation;
    }

    /**
     * Returns the version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getAuthor(): array
    {
        return $this->author;
    }

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getManualUrl(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTypoScriptConfig(): string
    {
        $typoScriptConfig = '';
        $typoScriptConfigAbsPath = $this->getTypoScriptConfigAbsPath();
        if (file_exists($typoScriptConfigAbsPath) && is_file($typoScriptConfigAbsPath)) {
            $typoScriptConfig = file_get_contents($typoScriptConfigAbsPath);
        }
        return $typoScriptConfig;
    }

    /**
     * @return string
     */
    public function getTypoScriptConfigAbsPath(): string
    {
        return $this->pathTsConfig;
    }

    /**
     * @return string
     */
    public function getTypoScriptConstantsAbsPath(): string
    {
        return $this->pathTyposcriptConstants;
    }

    /**
     * @return string
     */
    public function getTypoScriptAbsPath(): string
    {
        return $this->pathTyposcript;
    }

    /**
     * Builds the basic TypoScript constants
     *
     * @throws Exception
     */
    protected function buildBasicConstants(int $pid): void
    {
        $this->constants .= PHP_EOL . 'themes.relativePath = ' . $this->getRelativePath();
        $this->constants .= PHP_EOL . 'themes.resourcesPrivatePath = EXT:' . $this->getExtensionName() . '/Resources/Private/';
        $this->constants .= PHP_EOL . 'themes.resourcesPublicPath = EXT:' . $this->getExtensionName() . '/Resources/Public/';
        $this->constants .= PHP_EOL . 'themes.name = ' . $this->getExtensionName();
        $this->constants .= PHP_EOL . 'themes.templatePageId = ' . $pid;
        $this->constants .= PHP_EOL . 'themes.mode.context = ' . ApplicationContext::getApplicationContext();
        $this->constants .= PHP_EOL . 'themes.mode.isDevelopment = ' . (int)ApplicationContext::isDevelopmentModeActive();
        $this->constants .= PHP_EOL . 'themes.mode.isProduction = ' . (int)!ApplicationContext::isDevelopmentModeActive();
    }

    /**
     * Calculates the relative path to the theme directory for frontend usage.
     *
     * @return string
     */
    public function getRelativePath(): string
    {
        if (ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
            return PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath($this->getExtensionName()));
        }
        return '';
    }


    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    public function getConstants(): string
    {
        return $this->constants;
    }

    public function getSetup(): string
    {
        return $this->setup;
    }

    /**
     * Constants themes.languages.available and themes.languages.current.* (in a condition per language).
     */
    protected function buildTypoScriptForLanguage(): void
    {
        $prefix = 'themes.languages';
        /**
         * @todo fetch request object in another way!?
         */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $site = $request ? $request->getAttribute('site') : null;
        $languageUids = [];
        if ($site instanceof Site) {
            foreach ($site->getAllLanguages() as $siteLanguage) {
                $languageUid = $siteLanguage->getLanguageId();
                $languageUids[] = $languageUid;
                $this->constants .= '[siteLanguage("languageId") == ' . $languageUid . ']' . PHP_EOL;
                $this->constants .= $prefix . '.current {' . PHP_EOL;
                $this->constants .= ' uid = ' . $languageUid . PHP_EOL;
                $this->constants .= ' label = ' . $siteLanguage->getTitle() . PHP_EOL;
                $this->constants .= ' labelLocalized = ' . $siteLanguage->getNavigationTitle() . PHP_EOL;
                $this->constants .= ' labelEnglish = ' . $siteLanguage->getNavigationTitle() . PHP_EOL;
                $this->constants .= ' flag = ' . $siteLanguage->getFlagIdentifier() . PHP_EOL;
                $this->constants .= ' isoCode = ' . ($siteLanguage->toArray()['locale'] ?? (string)$siteLanguage->getLocale()) . PHP_EOL;
                // "iso-639-1" of the site configuration is deprecated since TYPO3 v12, the locale knows it
                $this->constants .= ' isoCodeShort = ' . $siteLanguage->getLocale()->getLanguageCode() . PHP_EOL;
                $this->constants .= ' isoCodeHtml = ' . $siteLanguage->getHreflang() . PHP_EOL;
                $this->constants .= '} ' . PHP_EOL;
                $this->constants .= '[end]' . PHP_EOL;
            }
        }
        $this->constants .= $prefix . '.available = ' . implode(',', $languageUids) . PHP_EOL;
    }

    /**
     * @param array<mixed> $row
     * @return void
     * @throws Exception
     */
    public function buildTypoScript(array $row): void
    {
        if ($this->constants === '' && $this->setup === '') {
            $this->buildBasicConstants($row['pid']);
            $this->buildTypoScriptForLanguage();
            //
            // Prepend theme TypoScript constants and setup
            $this->constants .= PHP_EOL . $this->getFileContent($this->pathTyposcriptConstants);
            $this->setup .= $this->getFileContent($this->pathTyposcript);
            //
            // Fetch selected feature
            $themeFeatures = GeneralUtility::trimExplode(
                ',',
                (string)($row['tx_themes_features'] ?? ''),
                true
            );
            foreach ($themeFeatures as $themeFeatureKey) {
                $this->getTypoScriptByKey($themeFeatureKey, 'feature');
            }
            //
            // Fetch selected extensions
            $themeExtensions = GeneralUtility::trimExplode(
                ',',
                (string)($row['tx_themes_extensions'] ?? ''),
                true
            );
            foreach ($themeExtensions as $themeExtensionKey) {
                $this->getTypoScriptByKey($themeExtensionKey, 'extension');
            }
        }
    }

    protected function getTypoScriptByKey(string $key, string $type): void
    {
        $relPath = '';
        $keyParts = explode('_', $key);
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($keyParts[0]);
        $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
        if ($type === 'feature') {
            $relPath = $extensionPath . 'Configuration/TypoScript/Features/' . $keyParts[1] . '/';
        } elseif ($type === 'extension') {
            $relPath = $extensionPath . 'Resources/Private/Extensions/' . $keyParts[1] . '/TypoScript/';
        }
        $this->constants .= PHP_EOL . $this->getFileContent(
                GeneralUtility::getFileAbsFileName($relPath . 'constants.typoscript')
            );
        $this->setup .= PHP_EOL . $this->getFileContent(
                GeneralUtility::getFileAbsFileName($relPath . 'setup.typoscript')
            );
    }

    protected function getFileContent(string $file): string
    {
        $content = '# File not found: ' . $file;
        if (file_exists($file)) {
            $content = file_get_contents($file);
        }
        return $content . PHP_EOL;
    }

    /**
     * Takes precedence over getDeniedTypoScriptConstants
     * @return array
     */
    public function getSupportedTypoScriptConstants(): array
    {
        return [];
    }

    public function isSupportedTypoScriptConstant(string $name): bool
    {
        $constants = $this->getSupportedTypoScriptConstants();
        if (isset($this->metaInformation['editor']['ignoreAllowedConstants']) && $this->metaInformation['editor']['ignoreAllowedConstants']) {
            return true;
        }
        if ($constants === []) {
            return true;
        }
        if ($constants[$name]['allowed'] ?? false) {
            return true;
        }
        return false;
    }

    public function getAlternativeLabelForSupportedTypoScriptConstant(string $name, string $fallback): string
    {
        return $this->getSupportedTypoScriptConstants()[$name]['label'] ?? $fallback;
    }
}
