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
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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
    /**
     * @var string
     */
    protected string $title;

    /**
     * @var array
     */
    protected array $author = [];

    /**
     * @var string
     */
    protected string $description;

    /**
     * @var string
     */
    protected string $version = '';

    /**
     * @var string
     */
    protected string $previewImage;

    /**
     * @var string
     */
    protected string $pathTyposcript;

    /**
     * @var string
     */
    protected string $pathTyposcriptConstants;

    /**
     * @var string
     */
    protected string $pathTsConfig;

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
    public function __construct(protected string $extensionName)
    {
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
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array $params Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
     * @param TemplateService $pObj Reference back to parent object, t3lib_tstemplate or one of its subclasses.
     * @param array $extensions Array of additional TypoScript for extensions
     * @param array $features Array of additional TypoScript for features
     *
     * @throws Exception
     */
    public function addTypoScriptForFe(array &$params, TemplateService &$pObj, array $extensions = [], array $features = [])
    {
        // @codingStandardsIgnoreStart
        $themeItem = [
                'constants' => @is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl(
                    $this->getTypoScriptConstantsAbsPath()
                ):'',
                'config' => @is_file($this->getTypoScriptAbsPath()) ? GeneralUtility::getUrl(
                    $this->getTypoScriptAbsPath()
                ):'',
                'include_static' => '',
                'include_static_file' => '',
                'title' => 'themes:' . $this->getExtensionName(),
                'uid' => md5($this->getExtensionName()),
        ];
        // @codingStandardsIgnoreEnd
        //
        $themeItem['constants'] .= $this->getBasicConstants($params['pid']);
        $themeItem['constants'] .= LF . $this->getTypoScriptForLanguage($params, $pObj);
        //
        $pObj->processTemplate(
            $themeItem,
            $params['idList'] . ',ext_themes' . str_replace('_', '', $this->getExtensionName()),
            $params['pid'],
            'ext_themes' . str_replace('_', '', $this->getExtensionName()),
            $params['templateId']
        );
        //
        // Additional TypoScript for extensions
        if (count($extensions) > 0) {
            foreach ($extensions as $extension) {
                $themeItem = $this->getTypoScriptDataForProcessing($extension);
                $pObj->processTemplate(
                    $themeItem,
                    $params['idList'] . ',ext_theme' . str_replace('_', '', $this->getExtensionName()),
                    $params['pid'],
                    'ext_theme' . str_replace('_', '', $this->getExtensionName()),
                    $params['templateId']
                );
            }
        }
        //
        // Additional TypoScript for features
        if (count($features) > 0) {
            foreach ($features as $feature) {
                $themeItem = $this->getTypoScriptDataForProcessing($feature, 'feature');
                $pObj->processTemplate(
                    $themeItem,
                    $params['idList'] . ',ext_theme' . str_replace('_', '', $this->getExtensionName()),
                    $params['pid'],
                    'ext_theme' . str_replace('_', '', $this->getExtensionName()),
                    $params['templateId']
                );
            }
        }
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
     * Returns the basic TypoScript constants
     *
     * @param $pid
     * @return string
     * @throws Exception
     */
    protected function getBasicConstants($pid): string
    {
        $buffer = LF . 'themes.relativePath = ' . $this->getRelativePath();
        $buffer .= LF . 'themes.name = ' . $this->getExtensionName();
        $buffer .= LF . 'themes.templatePageId = ' . $pid;
        $buffer .= LF . 'themes.mode.context = ' . ApplicationContext::getApplicationContext();
        $buffer .= LF . 'themes.mode.isDevelopment = ' . (int)ApplicationContext::isDevelopmentModeActive();
        $buffer .= LF . 'themes.mode.isProduction = ' . (int)!ApplicationContext::isDevelopmentModeActive();
        return $buffer;
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

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    /**
     *
     * @return string
     */
    public function getTypoScriptForLanguage(array &$params, TemplateService &$pObj): string
    {
        $outputBuffer = '';
        $key = 'themes.languages';
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $site = $request ? $request->getAttribute('site'):null;
        if ($site instanceof Site) {
            $languages = ArrayUtility::getValueByPath($site->getConfiguration(), 'languages', '.');
            if ((is_countable($languages) ? count($languages) : 0) > 0) {
                $languageUids = [];
                foreach ($languages as $key => $language) {
                    $languageUid = (int)$language['languageId'];
                    $languageUids[] = $languageUid;
                    $buffer = '[siteLanguage("languageId") == ' . $languageUid . ']' . LF;
                    $buffer .= $key . '.current {' . LF;
                    $buffer .= ' uid = ' . $languageUid . LF;
                    $buffer .= ' label = ' . $language['title'] . LF;
                    $buffer .= ' labelLocalized = ' . $language['navigationTitle'] . LF;
                    $buffer .= ' labelEnglish = ' . $language['navigationTitle'] . LF;
                    $buffer .= ' flag = ' . $language['flag'] . LF;
                    $buffer .= ' isoCode = ' . $language['locale'] . LF;
                    $buffer .= ' isoCodeShort = ' . $language['iso-639-1'] . LF;
                    $buffer .= ' isoCodeHtml = ' . $language['hreflang'] . LF;
                    $buffer .= '} ' . LF;
                    $buffer .= '[end]' . LF;
                    $outputBuffer .= $buffer;
                }
                $outputBuffer .= $key . '.available=' . implode(',', $languageUids) . LF;
            } else {
                $outputBuffer .= $key . '.available=' . LF;
            }
        } else {
            $outputBuffer .= $key . '.available=' . LF;
        }
        return $outputBuffer;
    }

    /**
     * @param string $key Key of the Extension or Feature
     * @param string $type Typ can be either extension or feature.
     * @return array
     */
    protected function getTypoScriptDataForProcessing(string $key, string $type = 'extension'): array
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
        $themeItem = [
                'constants' => '',
                'config' => '',
                'include_static' => '',
                'include_static_file' => '',
                'title' => 'themes:' . $this->getExtensionName() . ':' . $relPath,
                'uid' => md5($this->getExtensionName() . ':' . $relPath),
        ];
        //
        // TypoScript setup, if available
        $setupFile = GeneralUtility::getFileAbsFileName($relPath . 'setup.typoscript');
        if (file_exists($setupFile)) {
            $themeItem['config'] = file_get_contents($setupFile);
        }
        //
        // TypoScript constants, if available
        $constantsFile = GeneralUtility::getFileAbsFileName($relPath . 'constants.typoscript');
        if (file_exists($constantsFile)) {
            $themeItem['constants'] = file_get_contents($constantsFile);
        }
        //
        return $themeItem;
    }
}
