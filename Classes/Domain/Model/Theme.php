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

use Exception;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Form\Mvc\Configuration\YamlSource;

/**
 * Class Theme.
 *
 * the theme model object
 */
class Theme extends AbstractTheme
{
    /**
     * Constructs a new Theme.
     *
     * @param $extensionName
     * @throws Exception
     * @api
     */
    public function __construct($extensionName)
    {
        parent::__construct($extensionName);
        if (ExtensionManagementUtility::isLoaded($extensionName)) {
            // set needed path variables
            $path = ExtensionManagementUtility::extPath($this->getExtensionName());
            //
            $this->pathTyposcript = $path . 'Configuration/TypoScript/setup.typoscript';
            $this->pathTyposcriptConstants = $path . 'Configuration/TypoScript/constants.typoscript';
            $this->pathTsConfig = $path . 'Configuration/PageTS/tsconfig.typoscript';
            //
            $this->importExtEmConf();
            if (is_file(
                ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/Screenshots/screenshot.png'
            )) {
                $this->previewImage = PathUtility::stripPathSitePrefix(
                    ExtensionManagementUtility::extPath($this->getExtensionName())
                ) . 'Meta/Screenshots/screenshot.png';
            } else {
                $this->previewImage = PathUtility::stripPathSitePrefix(
                    ExtensionManagementUtility::extPath('themes')
                ) . 'Resources/Public/Images/screenshot.gif';
            }
            $yamlFile = ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml';
            if (file_exists($yamlFile)) {
                $yamlSource = GeneralUtility::makeInstance(YamlSource::class);
                $this->metaInformation = $yamlSource->load([$yamlFile]);
            } else {
                throw new Exception('No Yaml meta information found!');
            }
        }
    }

    /**
     * abstract the extension meta data import.
     */
    protected function importExtEmConf(): void
    {
        // @codingStandardsIgnoreStart
        $EM_CONF = [];
        $_EXTKEY = $this->extensionName;
        include ExtensionManagementUtility::extPath($this->getExtensionName()) . 'ext_emconf.php';
        // @codingStandardsIgnoreEnd
        $this->title = $EM_CONF[$this->getExtensionName()]['title'];
        $this->description = $EM_CONF[$this->getExtensionName()]['description'];
        $this->version = $EM_CONF[$this->getExtensionName()]['version'];
        $this->author['name'] = $EM_CONF[$this->getExtensionName()]['author'];
        $this->author['email'] = $EM_CONF[$this->getExtensionName()]['author_email'];
        $this->author['company'] = $EM_CONF[$this->getExtensionName()]['author_company'];
    }

    /**
     * Returns an array of preview images
     * @return array
     */
    public function getAllPreviewImages(): array
    {
        $buffer = $this->metaInformation['screenshots'] ?? [];
        if (is_array($buffer) && count($buffer) > 0) {
            foreach ($buffer as $key => $image) {
                // We need to use a real image file path, because in case of using a file
                // reference, a non admin backend user might not have access to the storage!
                $previewImage = GeneralUtility::getFileAbsFileName($image['file']);
                $previewImage = PathUtility::getAbsoluteWebPath($previewImage);
                $buffer[$key]['file'] = $previewImage;
            }
        }
        return $buffer ?? [];
    }

    /**
     * Return the TypoScript Config from the related file.
     *
     * @return string
     */
    public function getTypoScriptConfig(): string
    {
        if (file_exists($this->getTypoScriptConfigAbsPath()) && is_file($this->getTypoScriptConfigAbsPath())) {
            return file_get_contents($this->getTypoScriptConfigAbsPath());
        }
        return '';
    }

    /**
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array $params Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
     * @param TemplateService $pObj Reference back to parent object, t3lib_tstemplate or one of its subclasses.
     * @param array $extensions Array of additional TypoScript for extensions
     * @param array $features Array of additional TypoScript for features
     *
     * @throws \TYPO3\CMS\Install\Configuration\Exception
     */
    public function addTypoScriptForFe(array &$params, TemplateService &$pObj, array $extensions = [], array $features = []): void
    {
        /**
         * @todo v12 can be removed finally
         */


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

        // @todo resources Path / private Path
        $themeItem['constants'] .= PHP_EOL . 'themes.resourcesPrivatePath = ' . $this->getRelativePath(
        ) . 'Resources/Private/';
        $themeItem['constants'] .= PHP_EOL . 'themes.resourcesPublicPath = ' . $this->getRelativePath(
        ) . 'Resources/Public/';
        $themeItem['constants'] .= $this->getBasicConstants($params['pid']);
        $themeItem['constants'] .= PHP_EOL . $this->getTypoScriptForLanguage($params, $pObj);

        $pObj->processTemplate(
            $themeItem,
            $params['idList'] . ',ext_theme' . str_replace('_', '', $this->getExtensionName()),
            $params['pid'],
            'ext_theme' . str_replace('_', '', $this->getExtensionName()),
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
}
